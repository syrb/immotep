<?php

namespace App\Form;

use App\Entity\Apartment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class ApartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'appartement',
                'attr' => ['placeholder' => 'Ex: Appartement Centre-Ville']
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Adresse complète de l\'appartement']
            ])
            ->add('purchaseDate', DateType::class, [
                'label' => 'Date d\'achat',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('purchasePrice', MoneyType::class, [
                'label' => 'Prix d\'achat',
                'currency' => 'EUR',
                'attr' => ['placeholder' => 'Prix d\'achat de l\'appartement']
            ])
            ->add('rentalPrice', MoneyType::class, [
                'label' => 'Loyer mensuel',
                'currency' => 'EUR',
                'attr' => ['placeholder' => 'Loyer mensuel hors charges']
            ])
            ->add('charges', MoneyType::class, [
                'label' => 'Charges mensuelles',
                'currency' => 'EUR',
                'attr' => ['placeholder' => 'Montant des charges mensuelles']
            ])
            ->add('size', IntegerType::class, [
                'label' => 'Superficie (m²)',
                'attr' => ['placeholder' => 'Superficie en m²']
            ])
            ->add('bedrooms', IntegerType::class, [
                'label' => 'Nombre de chambres',
                'required' => false,
                'attr' => ['placeholder' => 'Nombre de chambres']
            ])
            ->add('bathrooms', IntegerType::class, [
                'label' => 'Nombre de salles de bain',
                'required' => false,
                'attr' => ['placeholder' => 'Nombre de salles de bain']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut de l\'appartement',
                'choices' => [
                    'Vacant' => 'Vacant',
                    'Loué' => 'Loué',
                    'En maintenance' => 'Maintenance',
                    'En vente' => 'En vente'
                ]
            ])
            ->add('tenantName', TextType::class, [
                'label' => 'Nom du locataire',
                'required' => false,
                'attr' => ['placeholder' => 'Nom du locataire actuel (si applicable)']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée de l\'appartement',
                    'rows' => 5
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Photo de l\'appartement',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Apartment::class,
        ]);
    }
}