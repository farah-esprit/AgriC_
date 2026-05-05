<?php

namespace App\Form;

use App\Entity\Culture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Culture>
 */
class CultureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la culture',
                'attr'  => ['placeholder' => 'Ex: Blé dur'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom de la culture est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 3,
                        'max'        => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\s\-\.]+$/u',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces, tirets ou points.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\p{L}]{3,}/u',
                        'message' => 'Le nom doit contenir au moins 3 lettres consécutives.',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label'   => 'Type de culture',
                'choices' => [
                    'Céréales'    => 'Céréales',
                    'Légumes'     => 'Légumes',
                    'Fruits'      => 'Fruits',
                    'Oléagineux'  => 'Oléagineux',
                    'Fourragères' => 'Fourragères',
                    'Autre'       => 'Autre',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner un type de culture.',
                    ]),
                    new Assert\Choice([
                        'choices' => ['Céréales', 'Légumes', 'Fruits', 'Oléagineux', 'Fourragères', 'Autre'],
                        'message' => 'Le type sélectionné est invalide.',
                    ]),
                ],
            ])
            ->add('superficie', NumberType::class, [
                'label' => 'Superficie (hectares)',
                'attr'  => ['step' => 0.1],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La superficie est obligatoire.',
                    ]),
                    new Assert\Positive([
                        'message' => 'La superficie doit être un nombre positif.',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value'   => 100000,
                        'message' => 'La superficie ne peut pas dépasser {{ compared_value }} hectares.',
                    ]),
                    new Assert\Type([
                        'type'    => 'numeric',
                        'message' => 'La superficie doit être un nombre valide.',
                    ]),
                ],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation/Région',
                'attr'  => ['placeholder' => 'Ex: Béja'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La localisation est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 3,
                        'max'        => 150,
                        'minMessage' => 'La localisation doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La localisation ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\s\-\,\.]+$/u',
                        'message' => 'La localisation ne peut contenir que des lettres, espaces, virgules, tirets ou points.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\p{L}]{3,}/u',
                        'message' => 'La localisation doit contenir au moins 3 lettres consécutives.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Culture::class,
        ]);
    }
}