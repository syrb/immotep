<?php

namespace App\Controller;

use App\Repository\ApartmentRepository;
use App\Repository\LeaseRepository;
use App\Repository\PaymentRepository;
use App\Repository\TenantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(
        Request $request,
        ApartmentRepository $apartmentRepository,
        TenantRepository $tenantRepository,
        LeaseRepository $leaseRepository,
        PaymentRepository $paymentRepository
    ): Response {
        // Récupérer les paramètres de filtrage de date
        $startDate = new \DateTime('first day of January this year');
        $endDate = new \DateTime();
        
        // Convertir les dates du format français (dd/mm/yyyy) au format PHP (yyyy-mm-dd)
        if ($request->query->has('startDate')) {
            $startDateString = $request->query->get('startDate');
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $startDateString, $matches)) {
                $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);
            }
        }
        
        if ($request->query->has('endDate')) {
            $endDateString = $request->query->get('endDate');
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $endDateString, $matches)) {
                $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);
            }
        }
        
        // Statistiques des appartements
        $apartments = $apartmentRepository->findAll();
        $totalApartments = count($apartments);
        $occupiedApartments = 0;
        $vacantApartments = 0;
        $maintenanceApartments = 0;
        
        foreach ($apartments as $apartment) {
            $status = $apartment->getStatus() ?? 'Vacant';
            if ($status === 'Loué') {
                $occupiedApartments++;
            } elseif ($status === 'Vacant') {
                $vacantApartments++;
            } elseif ($status === 'Maintenance') {
                $maintenanceApartments++;
            }
        }
        
        $occupancyRate = $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0;
        
        // Statistiques des locataires
        $tenants = $tenantRepository->findAll();
        $totalTenants = count($tenants);
        
        // Statistiques des baux
        $activeLeases = $leaseRepository->findActiveLeases();
        $totalActiveLeases = count($activeLeases);
        
        // Paiements pour les timeframes
        // Année courante
        $currentYearStart = new \DateTime('first day of January this year');
        $currentYearEnd = new \DateTime('last day of December this year');
        $currentYearPaidPayments = $paymentRepository->findPaidPaymentsBetweenDates($currentYearStart, $currentYearEnd);
        $currentYearIncome = array_sum(array_map(function($payment) { return $payment->getAmount(); }, $currentYearPaidPayments));
        
        // Année précédente
        $previousYearStart = new \DateTime('first day of January last year');
        $previousYearEnd = new \DateTime('last day of December last year');
        $previousYearPaidPayments = $paymentRepository->findPaidPaymentsBetweenDates($previousYearStart, $previousYearEnd);
        $previousYearIncome = array_sum(array_map(function($payment) { return $payment->getAmount(); }, $previousYearPaidPayments));
        
        // Période personnalisée
        $customPeriodPaidPayments = $paymentRepository->findPaidPaymentsBetweenDates($startDate, $endDate);
        $customPeriodIncome = array_sum(array_map(function($payment) { return $payment->getAmount(); }, $customPeriodPaidPayments));
        
        // Prochains paiements
        $today = new \DateTime();
        $threeMonthsLater = (new \DateTime())->modify('+3 months');
        $upcomingPayments = $paymentRepository->findUpcomingPaymentsBetweenDates($today, $threeMonthsLater);
        
        // Paiements impayés
        $latePayments = $paymentRepository->findLatePayments();
        $totalLateAmount = array_sum(array_map(function($payment) { return $payment->getAmount(); }, $latePayments));
        
        // Données mensuelles pour les graphiques
        $monthlyIncome = [];
        $monthlyExpenses = []; // À implémenter avec les dépenses réelles si disponibles
        
        // Remplir les données mensuelles pour l'année en cours
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = new \DateTime(sprintf('%s-%02d-01', $currentYearStart->format('Y'), $month));
            $monthEnd = (clone $monthStart)->modify('last day of this month');
            
            $monthlyPaidPayments = $paymentRepository->findPaidPaymentsBetweenDates($monthStart, $monthEnd);
            $monthlyIncomeAmount = array_sum(array_map(function($payment) { return $payment->getAmount(); }, $monthlyPaidPayments));
            
            $monthlyIncome[$month] = $monthlyIncomeAmount;
            $monthlyExpenses[$month] = 0; // À remplacer par les dépenses réelles
        }
        
        return $this->render('dashboard/index.html.twig', [
            'apartments' => $apartments,
            'totalApartments' => $totalApartments,
            'occupiedApartments' => $occupiedApartments,
            'vacantApartments' => $vacantApartments,
            'maintenanceApartments' => $maintenanceApartments,
            'occupancyRate' => $occupancyRate,
            'totalTenants' => $totalTenants,
            'totalActiveLeases' => $totalActiveLeases,
            'currentYearIncome' => $currentYearIncome,
            'previousYearIncome' => $previousYearIncome,
            'customPeriodIncome' => $customPeriodIncome,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'upcomingPayments' => $upcomingPayments,
            'latePayments' => $latePayments,
            'totalLateAmount' => $totalLateAmount,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpenses' => $monthlyExpenses,
        ]);
    }
}