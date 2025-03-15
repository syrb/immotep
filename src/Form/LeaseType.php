<?php

namespace App\Form;

use App\Entity\Apartment;
use App\Entity\Lease;
use App\Entity\Tenant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('apartment', EntityType::class, [
                'class' => Apartment::class,
                'choice_label' => 'name',
                'label' => 'Appartement',
                'placeholder' => 'Sélectionnez un appartement',
                'attr' => [
                    'class' => 'apartment-select'
                ]
            ])
            ->add('tenants', EntityType::class, [
                'class' => Tenant::class,
                'choice_label' => function (Tenant $tenant) {
                    return $tenant->getFirstName() . ' ' . $tenant->getLastName();
                },
                'label' => 'Locataires',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('rentalAmount', MoneyType::class, [
                'label' => 'Montant du loyer',
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => 'Montant mensuel hors charges',
                    'class' => 'rental-amount'
                ]
            ])
            ->add('chargesAmount', MoneyType::class, [
                'label' => 'Montant des charges',
                'currency' => 'EUR',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Montant mensuel des charges',
                    'class' => 'charges-amount'
                ]
            ])
            ->add('depositAmount', MoneyType::class, [
                'label' => 'Dépôt de garantie',
                'currency' => 'EUR',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Montant du dépôt de garantie'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut du bail',
                'choices' => [
                    'Actif' => 'Actif',
                    'En attente' => 'En attente',
                    'Terminé' => 'Terminé',
                    'Résilié' => 'Résilié'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Informations supplémentaires sur le bail'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lease::class,
        ]);
    }
}