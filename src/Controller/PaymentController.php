<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'app_payment_index', methods: ['GET'])]
    public function index(PaymentRepository $paymentRepository): Response
    {
        $summary = $paymentRepository->getPaymentsSummary();
        
        return $this->render('payment/index.html.twig', [
            'upcoming_payments' => $paymentRepository->findUpcomingPayments(5),
            'late_payments' => $paymentRepository->findLatePayments(),
            'all_payments' => $paymentRepository->findAllSortedByDueDate(),
            'summary' => $summary,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le paiement a été modifié avec succès.');
                
                // Redirection vers la page des paiements du bail
                return $this->redirectToRoute('app_lease_payments', ['id' => $payment->getLease()->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du paiement : ' . $e->getMessage());
            }
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/mark-as-paid', name: 'app_payment_mark_as_paid', methods: ['POST'])]
    public function markAsPaid(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('mark-as-paid'.$payment->getId(), $request->request->get('_token'))) {
            try {
                $method = $request->request->get('method', 'Virement');
                $payment->markAsPaid($method);
                $payment->setPaymentDate(new \DateTime()); // Set payment date to today
                $entityManager->flush();
                $this->addFlash('success', 'Le paiement a été marqué comme payé.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_payment_index'));
    }

    #[Route('/{id}/mark-as-unpaid', name: 'app_payment_mark_as_unpaid', methods: ['POST'])]
    public function markAsUnpaid(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('mark-as-unpaid'.$payment->getId(), $request->request->get('_token'))) {
            try {
                $payment->markAsUnpaid();
                $entityManager->flush();
                $this->addFlash('success', 'Le paiement a été marqué comme non payé.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_payment_index'));
    }
    
    #[Route('/bulk-action', name: 'app_payment_bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager): Response
    {
        $action = $request->request->get('action');
        $paymentIds = $request->request->get('payments', []);
        
        if (empty($paymentIds)) {
            $this->addFlash('error', 'Aucun paiement sélectionné.');
            return $this->redirectToRoute('app_payment_index');
        }
        
        $method = $request->request->get('payment_method', 'Virement');
        $count = 0;
        
        foreach ($paymentIds as $id) {
            $payment = $paymentRepository->find($id);
            if (!$payment) continue;
            
            if ($action === 'mark_paid') {
                $payment->markAsPaid($method);
                $count++;
            } elseif ($action === 'mark_unpaid') {
                $payment->markAsUnpaid();
                $count++;
            }
        }
        
        if ($count > 0) {
            $entityManager->flush();
            $this->addFlash('success', $count . ' paiement(s) ont été mis à jour.');
        }
        
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_payment_index'));
    }
}