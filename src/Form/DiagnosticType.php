<?php

namespace App\Form;

use App\Entity\Diagnostic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DiagnosticType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDiagnostic', TextType::class, [
                'label' => 'Date du diagnostic',
                'attr'  => ['placeholder' => 'YYYY-MM-DD'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date du diagnostic est obligatoire.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
                        'message' => 'Le format doit être YYYY-MM-DD.',
                    ]),
                    new Assert\Callback(function ($value, $context) {
                        if (!$value) return;
                        $date = \DateTime::createFromFormat('Y-m-d', $value);
                        if (!$date || $date->format('Y-m-d') !== $value) {
                            $context->buildViolation('La date saisie est invalide.')
                                ->addViolation();
                            return;
                        }
                        if ($date > new \DateTime('today')) {
                            $context->buildViolation('La date ne peut pas être dans le futur.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('symptomes', TextareaType::class, [
                'label' => 'Symptômes observés',
                'attr'  => ['rows' => 4],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Les symptômes sont obligatoires.',
                    ]),
                    new Assert\Length([
                        'min'        => 10,
                        'max'        => 2000,
                        'minMessage' => 'Les symptômes doivent contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Les symptômes ne peuvent pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('informationsComplementaires', TextareaType::class, [
                'label'    => 'Informations complémentaires',
                'required' => false,
                'attr'     => ['rows' => 4],
                'constraints' => [
                    new Assert\Length([
                        'max'        => 3000,
                        'maxMessage' => 'Les informations complémentaires ne peuvent pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('idCulture', IntegerType::class, [
                'label' => 'ID de la culture concernée',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "L'ID de la culture est obligatoire.",
                    ]),
                    new Assert\Positive([
                        'message' => "L'ID de la culture doit être un entier positif.",
                    ]),
                    new Assert\LessThanOrEqual([
                        'value'   => 999999,
                        'message' => "L'ID de la culture ne peut pas dépasser {{ compared_value }}.",
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Diagnostic::class,
        ]);
    }
}