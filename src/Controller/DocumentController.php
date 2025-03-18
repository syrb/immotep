<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/document')]
class DocumentController extends AbstractController
{
    #[Route('/', name: 'app_document_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DocumentRepository $documentRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les années pour lesquelles l'utilisateur a des documents (pour le filtre)
        $years = $documentRepository->findYearsWithDocuments($user->getId());
        $yearsList = array_map(function($yearData) {
            return $yearData['year'];
        }, $years);
        
        // Récupérer l'année sélectionnée pour le filtrage ou utiliser toutes les années
        $filterYear = $request->query->get('year', null);
        
        // Créer un nouveau document
        $document = new Document();
        $document->setDocumentDate(new \DateTime());
        $document->setOwner($user);
        
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        // Traitement du formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload du fichier
            $documentFile = $form->get('documentFile')->getData();
            if ($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->guessExtension();

                try {
                    // Vérifier que le répertoire existe
                    $targetDirectory = $this->getParameter('documents_directory');
                    if (!is_dir($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    
                    // Déplacer le fichier
                    $documentFile->move(
                        $targetDirectory,
                        $newFilename
                    );
                    
                    $document->setFilename($newFilename);
                    $document->setOriginalFilename($documentFile->getClientOriginalName());
                    $document->setMimeType($documentFile->getClientMimeType());
                    
                    $entityManager->persist($document);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Le document a été ajouté avec succès.');
                    
                    // Rediriger avec le filtre de l'année du document ajouté
                    $year = $document->getDocumentDate()->format('Y');
                    return $this->redirectToRoute('app_document_index', ['year' => $year]);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload du fichier: ' . $e->getMessage());
                }
            }
        }

        // Récupérer les documents en fonction du filtre avec une limite
        if ($filterYear) {
            $documents = $documentRepository->findByYear($filterYear, $user->getId());
            $selectedYear = $filterYear;
        } else {
            $documents = $documentRepository->findAllByUser($user->getId());
            $selectedYear = null;
        }

        return $this->render('document/index.html.twig', [
            'years' => $yearsList,
            'selectedYear' => $selectedYear,
            'documents' => $documents,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du document
        if ($this->getUser() !== $document->getOwner()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à ce document.');
        }
        
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du document
        if ($this->getUser() !== $document->getOwner()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce document.');
        }
        
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload du fichier
            $documentFile = $form->get('documentFile')->getData();
            if ($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->guessExtension();

                try {
                    // Vérifier que le répertoire existe
                    $targetDirectory = $this->getParameter('documents_directory');
                    if (!is_dir($targetDirectory)) {
                        mkdir($targetDirectory, 0777, true);
                    }
                    
                    $documentFile->move(
                        $targetDirectory,
                        $newFilename
                    );
                    
                    // Supprimer l'ancien fichier
                    $oldFilename = $document->getFilename();
                    if ($oldFilename) {
                        $oldFilePath = $targetDirectory . '/' . $oldFilename;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $document->setFilename($newFilename);
                    $document->setOriginalFilename($documentFile->getClientOriginalName());
                    $document->setMimeType($documentFile->getClientMimeType());
                } catch (FileException $e) {
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload du fichier: ' . $e->getMessage());
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le document a été modifié avec succès.');
            
            // Rediriger avec le filtre de l'année du document modifié
            $year = $document->getDocumentDate()->format('Y');
            return $this->redirectToRoute('app_document_index', ['year' => $year], Response::HTTP_SEE_OTHER);
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du document
        if ($this->getUser() !== $document->getOwner()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer ce document.');
        }
        
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier
            $filename = $document->getFilename();
            if ($filename) {
                $filePath = $this->getParameter('documents_directory') . '/' . $filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $year = $document->getDocumentDate()->format('Y');
            $entityManager->remove($document);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le document a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_document_index', ['year' => $year ?? null], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/download', name: 'app_document_download', methods: ['GET'])]
    public function download(Document $document): Response
    {
        // Vérifier que l'utilisateur est bien le propriétaire du document
        if ($this->getUser() !== $document->getOwner()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à télécharger ce document.');
        }
        
        $filePath = $this->getParameter('documents_directory') . '/' . $document->getFilename();
        
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Le fichier demandé n\'existe pas.');
        }
        
        // Définir le nom du fichier téléchargé (utiliser le nom original si disponible)
        $downloadName = $document->getOriginalFilename() ?: $document->getFilename();
        
        return $this->file($filePath, $downloadName);
    }
}