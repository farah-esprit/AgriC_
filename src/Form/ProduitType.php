<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr'  => [
                    'placeholder' => 'Ex: Engrais Bio',
                    'pattern'     => '[A-Za-zÀ-ÿ\s\-]+',
                    'title'       => 'Le nom ne peut contenir que des lettres.',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le nom du produit est obligatoire.'),
                    new Length(
                        min: 3,
                        max: 255,
                        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.'
                    ),
                    new Regex(
                        pattern: '/^[A-Za-zÀ-ÿ\s\-]+$/',
                        message: 'Le nom ne peut contenir que des lettres.'
                    ),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(message: 'La description est obligatoire.'),
                    new Length(
                        min: 10,
                        minMessage: 'La description doit contenir au moins {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('prix', MoneyType::class, [
                'label'    => 'Prix',
                'currency' => 'TND',
                'attr'     => [
                    'min'         => 0,
                    'step'        => '0.01',
                    'placeholder' => 'Ex: 15.00',
                    'onkeypress'  => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le prix est obligatoire.'),
                    new Positive(message: 'Le prix doit être un nombre strictement positif.'),
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'label'       => 'Catégorie',
                'placeholder' => '-- Choisir une catégorie --',
                'choices'     => [
                    'Engrais'    => 'Engrais',
                    'Semences'   => 'Semences',
                    'Outils'     => 'Outils',
                    'Pesticides' => 'Pesticides',
                    'Autre'      => 'Autre',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez choisir une catégorie.'),
                ],
            ])
            ->add('actif', CheckboxType::class, [
                'label'    => 'Disponible à la vente',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label'    => 'Image du produit (JPG, PNG)',
                'mapped'   => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize'          => '2M',
                        'maxSizeMessage'   => 'L\'image ne doit pas dépasser 2 Mo.',
                        'mimeTypes'        => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG).',
                    ]),
                ],
            ])
            ->add('promo', CheckboxType::class, [
                'label'    => 'En promotion',
                'required' => false,
            ])
            ->add('tauxPromo', NumberType::class, [
                'label'    => 'Taux de promotion (%)',
                'required' => false,
                'attr'     => [
                    'min'         => 0,
                    'max'         => 100,
                    'placeholder' => 'Ex: 10',
                    'onkeypress'  => 'return (event.charCode >= 48 && event.charCode <= 57)',
                ],
                'constraints' => [
                    new Range(
                        min: 0,
                        max: 100,
                        notInRangeMessage: 'Le taux de promotion doit être entre {{ min }}% et {{ max }}%.'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}