<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<User>
 */
class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr'  => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire',
                    ]),
                    new Assert\Length([
                        'min'        => 3,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'max'        => 50,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/',
                        'message' => 'Le nom ne peut contenir que des lettres, espaces ou tirets',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label'    => 'Adresse email',
                'attr'     => ['class' => 'form-control'],
                'disabled' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => "L'email est obligatoire"]),
                    new Assert\Email(['message' => 'Email invalide']),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label'    => 'Téléphone tunisien',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'placeholder' => '12345678',
                    'maxlength'   => 8,
                    'inputmode'   => 'numeric',
                    'pattern'     => '[0-9]{8}',
                ],
                'constraints' => [
                    new Assert\Callback(function ($value, ExecutionContextInterface $context) {
                        if (!empty($value) && !preg_match('/^[0-9]{8}$/', $value)) {
                            $context->buildViolation('Le numéro doit contenir exactement 8 chiffres')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'     => PasswordType::class,
                'mapped'   => false,
                'required' => false,
                'first_options'  => [
                    'label' => 'Nouveau mot de passe',
                    'attr'  => [
                        'class'       => 'form-control',
                        'placeholder' => 'Laisser vide pour ne pas changer',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => ['class' => 'form-control'],
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
                    new Assert\Length([
                        'min'        => 8,
                        'minMessage' => 'Minimum {{ limit }} caractères',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    // ✅ NOM UNIQUE pour ce formulaire
    public function getBlockPrefix(): string
    {
        return 'user_edit_form';
    }
}