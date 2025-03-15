<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Form\TenantType;
use App\Repository\TenantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/tenant')]
class TenantController extends AbstractController
{
    #[Route('/', name: 'app_tenant_index', methods: ['GET'])]
    public function index(TenantRepository $tenantRepository): Response
    {
        return $this->render('tenant/index.html.twig', [
            'tenants' => $tenantRepository->findAllSortedByName(),
        ]);
    }

    #[Route('/new', name: 'app_tenant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $tenant = new Tenant();
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de pièce d'identité
            $this->handleFileUpload($form, 'idCardFile', $tenant, 'setIdCardFilename', $slugger, 'tenant_documents');
            
            // Gestion de l'upload de contrat de bail
            $this->handleFileUpload($form, 'leaseContractFile', $tenant, 'setLeaseContractFilename', $slugger, 'tenant_documents');
            
            // Gestion de l'upload de document du garant
            $this->handleFileUpload($form, 'guarantorDocumentFile', $tenant, 'setGuarantorDocumentFilename', $slugger, 'tenant_documents');

            try {
                $entityManager->persist($tenant);
                $entityManager->flush();
                $this->addFlash('success', 'Le locataire a été créé avec succès.');
                return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('tenant/new.html.twig', [
            'tenant' => $tenant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tenant_show', methods: ['GET'])]
    public function show(Tenant $tenant): Response
    {
        return $this->render('tenant/show.html.twig', [
            'tenant' => $tenant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tenant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tenant $tenant, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(TenantType::class, $tenant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de pièce d'identité
            $this->handleFileUpload($form, 'idCardFile', $tenant, 'setIdCardFilename', $slugger, 'tenant_documents');
            
            // Gestion de l'upload de contrat de bail
            $this->handleFileUpload($form, 'leaseContractFile', $tenant, 'setLeaseContractFilename', $slugger, 'tenant_documents');
            
            // Gestion de l'upload de document du garant
            $this->handleFileUpload($form, 'guarantorDocumentFile', $tenant, 'setGuarantorDocumentFilename', $slugger, 'tenant_documents');

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le locataire a été modifié avec succès.');
                return $this->redirectToRoute('app_tenant_show', ['id' => $tenant->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage());
            }
        }

        return $this->render('tenant/edit.html.twig', [
            'tenant' => $tenant,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tenant_delete', methods: ['POST'])]
    public function delete(Request $request, Tenant $tenant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tenant->getId(), $request->request->get('_token'))) {
            // Supprimer les fichiers associés
            $this->removeFileIfExists($tenant->getIdCardFilename(), 'tenant_documents');
            $this->removeFileIfExists($tenant->getLeaseContractFilename(), 'tenant_documents');
            $this->removeFileIfExists($tenant->getGuarantorDocumentFilename(), 'tenant_documents');
            
            try {
                $entityManager->remove($tenant);
                $entityManager->flush();
                $this->addFlash('success', 'Le locataire a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_tenant_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Gérer l'upload de fichier
     */
    private function handleFileUpload($form, string $fieldName, Tenant $tenant, string $setterMethod, SluggerInterface $slugger, string $directory): void
    {
        $file = $form->get($fieldName)->getData();
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter($directory),
                    $newFilename
                );
                
                // Supprimer l'ancien fichier si nécessaire
                $oldGetterMethod = str_replace('set', 'get', $setterMethod);
                $oldFilename = $tenant->$oldGetterMethod();
                if ($oldFilename) {
                    $this->removeFileIfExists($oldFilename, $directory);
                }
                
                // Définir le nouveau nom de fichier
                $tenant->$setterMethod($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Un problème est survenu lors de l\'upload du fichier ' . $fieldName);
            }
        }
    }

    /**
     * Supprimer un fichier s'il existe
     */
    private function removeFileIfExists(?string $filename, string $directory): void
    {
        if ($filename) {
            $filePath = $this->getParameter($directory) . '/' . $filename;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}