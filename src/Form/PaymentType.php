<?php

namespace App\Form;

use App\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dueDate', DateType::class, [
                'label' => 'Date d\'échéance',
                'widget' => 'single_text',
                'html5' => true
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Montant',
                'currency' => 'EUR'
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Payé' => 'Payé',
                    'Non payé' => 'Non payé',
                    'Payé partiellement' => 'Payé partiellement'
                ]
            ])
            ->add('paymentDate', DateType::class, [
                'label' => 'Date de paiement',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Méthode de paiement',
                'choices' => [
                    'Virement bancaire' => 'Virement',
                    'Chèque' => 'Chèque',
                    'Espèces' => 'Espèces',
                    'Prélèvement automatique' => 'Prélèvement',
                    'Autre' => 'Autre'
                ],
                'required' => false
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Informations supplémentaires'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}