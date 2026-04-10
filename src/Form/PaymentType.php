<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardholderName', TextType::class, [
                'label'       => 'Nom sur la carte',
                'mapped'      => false,
                'attr'        => ['placeholder' => 'Ex: Jean Dupont'],
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                ],
            ])
            ->add('cardNumber', TextType::class, [
                'label'       => 'Numéro de carte',
                'mapped'      => false,
                'attr'        => [
                    'placeholder' => '4242 4242 4242 4242',
                    'maxlength'   => 19,
                ],
                'constraints' => [
                    new NotBlank(message: 'Le numéro de carte est obligatoire.'),
                    new Regex(
                        pattern: '/^\d{4}\s?\d{4}\s?\d{4}\s?\d{4}$/',
                        message: 'Numéro de carte invalide.'
                    ),
                ],
            ])
            ->add('expiryDate', TextType::class, [
                'label'       => 'Date d\'expiration',
                'mapped'      => false,
                'attr'        => ['placeholder' => 'MM/AA'],
                'constraints' => [
                    new NotBlank(message: 'La date d\'expiration est obligatoire.'),
                    new Regex(
                        pattern: '/^(0[1-9]|1[0-2])\/\d{2}$/',
                        message: 'Format invalide. Utilisez MM/AA.'
                    ),
                ],
            ])
            ->add('cvv', TextType::class, [
                'label'       => 'CVV',
                'mapped'      => false,
                'attr'        => [
                    'placeholder' => '123',
                    'maxlength'   => 4,
                ],
                'constraints' => [
                    new NotBlank(message: 'Le CVV est obligatoire.'),
                    new Length(
                        min: 3,
                        max: 4,
                        minMessage: 'Le CVV doit contenir au moins 3 chiffres.',
                        maxMessage: 'Le CVV ne peut pas dépasser 4 chiffres.'
                    ),
                    new Regex(
                        pattern: '/^\d{3,4}$/',
                        message: 'Le CVV doit contenir uniquement des chiffres.'
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}