<?php

namespace App\Controller;

use App\Entity\Apartment;
use App\Form\ApartmentType;
use App\Repository\ApartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/apartment')]
class ApartmentController extends AbstractController
{
    #[Route('/', name: 'app_apartment_index', methods: ['GET'])]
    public function index(ApartmentRepository $apartmentRepository): Response
    {
        return $this->render('apartment/index.html.twig', [
            'apartments' => $apartmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_apartment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $apartment = new Apartment();
        $form = $this->createForm(ApartmentType::class, $apartment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('apartments_images_directory'),
                        $newFilename
                    );
                    $apartment->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload de l\'image');
                }
            }

            try {
                $entityManager->persist($apartment);
                $entityManager->flush();
                $this->addFlash('success', 'L\'appartement a été créé avec succès.');
                return $this->redirectToRoute('app_apartment_show', ['id' => $apartment->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création : ' . $e->getMessage());
            }
        }

        return $this->render('apartment/new.html.twig', [
            'apartment' => $apartment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_apartment_show', methods: ['GET'])]
    public function show(Apartment $apartment): Response
    {
        return $this->render('apartment/show.html.twig', [
            'apartment' => $apartment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_apartment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Apartment $apartment, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ApartmentType::class, $apartment);
        // Appel unique à handleRequest
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('apartments_images_directory'),
                        $newFilename
                    );
                    
                    // Supprimer l'ancienne image si elle existe
                    $oldFilename = $apartment->getImageFilename();
                    if ($oldFilename) {
                        $oldFilePath = $this->getParameter('apartments_images_directory').'/'.$oldFilename;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    $apartment->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Un problème est survenu lors de l\'upload de l\'image: ' . $e->getMessage());
                }
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'L\'appartement a été modifié avec succès.');
                return $this->redirectToRoute('app_apartment_show', ['id' => $apartment->getId()], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification: ' . $e->getMessage());
            }
        }

        return $this->render('apartment/edit.html.twig', [
            'apartment' => $apartment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_apartment_delete', methods: ['POST'])]
    public function delete(Request $request, Apartment $apartment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apartment->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            $filename = $apartment->getImageFilename();
            if ($filename) {
                $filePath = $this->getParameter('apartments_images_directory').'/'.$filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $entityManager->remove($apartment);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'appartement a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_apartment_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/info', name: 'app_apartment_info', methods: ['GET'])]
    public function getInfo(Apartment $apartment): Response
    {
        // Renvoyer les informations de l'appartement au format JSON
        return $this->json([
            'id' => $apartment->getId(),
            'name' => $apartment->getName(),
            'rentalPrice' => $apartment->getRentalPrice(),
            'chargesAmount' => $apartment->getCharges(),
            'size' => $apartment->getSize(),
            'bedrooms' => $apartment->getBedrooms(),
            'bathrooms' => $apartment->getBathrooms()
        ]);
    }
}