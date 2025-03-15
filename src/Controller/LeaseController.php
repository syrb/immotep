<?php

namespace App\Controller;

use App\Entity\Lease;
use App\Entity\Payment;
use App\Form\LeaseType;
use App\Repository\LeaseRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lease')]
class LeaseController extends AbstractController
{
    #[Route('/', name: 'app_lease_index', methods: ['GET'])]
    public function index(LeaseRepository $leaseRepository): Response
    {
        return $this->render('lease/index.html.twig', [
            'leases' => $leaseRepository->findAllSortedByStartDate(),
        ]);
    }

    #[Route('/new', name: 'app_lease_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lease = new Lease();
        
        // Définir des dates par défaut
        $lease->setStartDate(new \DateTime());
        $lease->setEndDate(new \DateTime('+1 year'));
        
        $form = $this->createForm(LeaseType::class, $lease);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Persister d'abord le bail
                $entityManager->persist($lease);
                $entityManager->flush();
                
                // Générer les paiements mensuels
                $lease->generateMonthlyPayments();
                
                // Sauvegarder les paiements générés
                $entityManager->flush();
                
                $this->addFlash('success', 'Le bail et les paiements mensuels ont été créés avec succès.');
                return $this->redirectToRoute('app_lease_show', ['id' => $lease->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du bail : ' . $e->getMessage());
            }
        }

        return $this->render('lease/new.html.twig', [
            'lease' => $lease,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_lease_show', methods: ['GET'])]
    public function show(Lease $lease, PaymentRepository $paymentRepository): Response
    {
        // Récupérer tous les paiements associés à ce bail
        $payments = $paymentRepository->findByLease($lease->getId());
        
        return $this->render('lease/show.html.twig', [
            'lease' => $lease,
            'payments' => $payments,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lease_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lease $lease, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LeaseType::class, $lease);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Mettre à jour les montants des paiements si le loyer a changé
                $totalAmount = $lease->getRentalAmount() + ($lease->getChargesAmount() ?? 0);
                foreach ($lease->getPayments() as $payment) {
                    if ($payment->getStatus() !== 'Payé') {
                        $payment->setAmount($totalAmount);
                    }
                }
                
                // Générer de nouvelles échéances si les dates ont changé
                $lease->generateMonthlyPayments();
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Le bail a été modifié avec succès.');
                return $this->redirectToRoute('app_lease_show', ['id' => $lease->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du bail : ' . $e->getMessage());
            }
        }

        return $this->render('lease/edit.html.twig', [
            'lease' => $lease,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_lease_delete', methods: ['POST'])]
    public function delete(Request $request, Lease $lease, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lease->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($lease);
                $entityManager->flush();
                $this->addFlash('success', 'Le bail a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du bail : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_lease_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/{id}/payments', name: 'app_lease_payments', methods: ['GET'])]
    public function payments(Lease $lease, PaymentRepository $paymentRepository): Response
    {
        $payments = $paymentRepository->findByLease($lease->getId());
        
        return $this->render('lease/payments.html.twig', [
            'lease' => $lease,
            'payments' => $payments,
        ]);
    }
    
    #[Route('/{id}/regenerate-payments', name: 'app_lease_regenerate_payments', methods: ['GET', 'POST'])]
    public function regeneratePayments(Lease $lease, EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            // Générer à nouveau les paiements mensuels
            $lease->generateMonthlyPayments();
            $entityManager->flush();
            
            $this->addFlash('success', 'Les paiements ont été régénérés avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la régénération des paiements : ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('app_lease_payments', ['id' => $lease->getId()]);
    }
}