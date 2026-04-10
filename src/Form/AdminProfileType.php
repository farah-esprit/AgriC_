<?php

namespace App\Form;

use App\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AdminProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // ── PHOTO DE PROFIL (non mappé → gérer manuellement dans le contrôleur) ──
            ->add('photoFile', FileType::class, [
                'label'    => 'Photo de profil',
                'mapped'   => false,
                'required' => false,
                'attr'     => [
                    'class'  => 'form-control',
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'id'     => 'photoFileInput',
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize'          => '2M',
                        'maxSizeMessage'   => 'La photo ne doit pas dépasser 2 Mo.',
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Formats acceptés : JPEG, PNG, WebP.',
                    ]),
                ],
            ])

            // ── NOM : lettres uniquement, 2 à 50 caractères ──
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Ben Ali',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
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

            // ── PRÉNOM : lettres uniquement, 2 à 50 caractères ──
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Ex: Ahmed',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est obligatoire.']),
                    new Assert\Length([
                        'min'        => 3,
                        'max'        => 50,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le prénom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le prénom ne peut contenir que des lettres, espaces ou tirets.',
                    ]),
                ],
            ])

            // ── EMAIL : désactivé (non modifiable) ──
            ->add('email', EmailType::class, [
                'label'    => 'Adresse email',
                'disabled' => true,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => 'exemple@email.com',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => "L'email est obligatoire."]),
                    new Assert\Email(['message' => "L'adresse email '{{ value }}' n'est pas valide."]),
                ],
            ])

            // ── TÉLÉPHONE : exactement 8 chiffres tunisiens ──
            ->add('telephone', TextType::class, [
                'label'    => 'Téléphone tunisien',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => '12345678',
                    'maxlength'   => '8',
                    'inputmode'   => 'numeric',
                    'pattern'     => '[0-9]{8}',
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

            // ── MOT DE PASSE : optionnel, min 8 car., maj + min + chiffre ──
            ->add('plainPassword', RepeatedType::class, [
                'type'     => PasswordType::class,
                'mapped'   => false,
                'required' => false,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'attr'  => [
                        'class'       => 'form-control',
                        'placeholder' => 'Min. 8 car., maj + min + chiffre',
                        'autocomplete'=> 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => [
                        'class'       => 'form-control',
                        'placeholder' => 'Répétez le mot de passe',
                        'autocomplete'=> 'new-password',
                    ],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new Assert\Length([
                        'min'        => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
                    ]),
                ],
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr'  => ['class' => 'btn btn-success btn-lg px-5'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'admin_profile_form';
    }
}