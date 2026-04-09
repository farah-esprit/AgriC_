<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── NOM : lettres uniquement, 3 à 50 caractères ──
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Ahmed Ben Ali',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 3,
                        'max'        => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces ou tirets.',
                    ]),
                ],
            ])

            // ── EMAIL ──
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'exemple@email.com',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => "L'email est obligatoire.",
                    ]),
                    new Assert\Email([
                        'message' => "L'adresse email '{{ value }}' n'est pas valide.",
                    ]),
                ],
            ])

            // ── TÉLÉPHONE : exactement 8 chiffres ──
            ->add('telephone', TextType::class, [
                'label'    => 'Téléphone',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => '12345678',
                    'maxlength'   => '8',
                    'oninput'     => 'this.value=this.value.replace(/[^0-9]/g,"")',
                ],
                'constraints' => [
                    new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                        if (!empty($value) && !preg_match('/^[0-9]{8}$/', $value)) {
                            $context->buildViolation('Le numéro doit contenir exactement 8 chiffres.')
                                ->addViolation();
                        }
                    }),
                ],
            ])

            // ── RÔLE ──
            ->add('role', ChoiceType::class, [
                'label'   => 'Rôle',
                'choices' => [
                    'Agriculteur' => 'AGRICULTEUR',
                    'Fournisseur' => 'FOURNISSEUR',
                    'Expert'      => 'EXPERT',
                ],
                'attr' => ['class' => 'form-select'],
            ])

            // ── MOT DE PASSE : min 8 car., maj + min + chiffre obligatoires ──
            ->add('plainPassword', RepeatedType::class, [
                'type'   => PasswordType::class,
                'mapped' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le mot de passe est obligatoire.',
                    ]),
                    new Assert\Length([
                        'min'        => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
                    ]),
                ],
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr'  => [
                        'class'       => 'form-control',
                        'placeholder' => 'Min. 8 caractères, maj + min + chiffre',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => [
                        'class'       => 'form-control',
                        'placeholder' => 'Répétez le mot de passe',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])

            ->add('submit', SubmitType::class, [
                'label' => "S'inscrire",
                'attr'  => ['class' => 'btn btn-success w-100'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}