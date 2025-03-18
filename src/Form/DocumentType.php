<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du document',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir un titre pour ce document'])
                ],
                'attr' => ['placeholder' => 'Titre du document']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description du document (optionnel)',
                    'rows' => 3
                ]
            ])
            ->add('documentDate', DateType::class, [
                'label' => 'Date du document',
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'data' => new \DateTime(),
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Facture' => 'Facture',
                    'Contrat' => 'Contrat',
                    'Quittance' => 'Quittance',
                    'Administrative' => 'Administrative',
                    'Autre' => 'Autre'
                ],
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => false
            ])
            ->add('documentFile', FileType::class, [
                'label' => 'Fichier',
                'mapped' => true,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'image/jpeg',
                            'image/png',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, Word, Excel, JPEG, PNG)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}