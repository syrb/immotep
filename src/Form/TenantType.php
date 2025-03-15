<?php

namespace App\Form;

use App\Entity\Apartment;
use App\Entity\Tenant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom du locataire']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom du locataire']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Adresse email du locataire']
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => 'Numéro de téléphone']
            ])
            ->add('birthDate', BirthdayType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Adresse complète du locataire',
                    'rows' => 3
                ]
            ])
            ->add('apartment', EntityType::class, [
                'class' => Apartment::class,
                'choice_label' => 'name',
                'label' => 'Appartement associé',
                'required' => false,
                'placeholder' => 'Sélectionnez un appartement (optionnel)'
            ])
            ->add('guarantorName', TextType::class, [
                'label' => 'Nom du garant',
                'required' => false,
                'attr' => ['placeholder' => 'Nom complet du garant']
            ])
            ->add('guarantorEmail', EmailType::class, [
                'label' => 'Email du garant',
                'required' => false,
                'attr' => ['placeholder' => 'Adresse email du garant']
            ])
            ->add('guarantorPhone', TelType::class, [
                'label' => 'Téléphone du garant',
                'required' => false,
                'attr' => ['placeholder' => 'Numéro de téléphone du garant']
            ])
            ->add('guarantorAddress', TextareaType::class, [
                'label' => 'Adresse du garant',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Adresse complète du garant',
                    'rows' => 3
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Informations complémentaires',
                    'rows' => 4
                ]
            ])
            ->add('idCardFile', FileType::class, [
                'label' => 'Pièce d\'identité',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou une image valide',
                    ])
                ],
            ])
            ->add('leaseContractFile', FileType::class, [
                'label' => 'Contrat de bail',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou une image valide',
                    ])
                ],
            ])
            ->add('guarantorDocumentFile', FileType::class, [
                'label' => 'Document du garant',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '8M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou une image valide',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tenant::class,
        ]);
    }
}