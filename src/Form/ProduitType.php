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

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Engrais Bio']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'TND'
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Engrais' => 'Engrais',
                    'Semences' => 'Semences',
                    'Outils' => 'Outils',
                    'Pesticides' => 'Pesticides',
                    'Autre' => 'Autre'
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Disponible à la vente',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image du produit (JPG, PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG).',
                    ])
                ],
            ])
            ->add('promo', CheckboxType::class, [
                'label' => 'En promotion',
                'required' => false,
            ])
            ->add('tauxPromo', NumberType::class, [
                'label' => 'Taux de promotion (%)',
                'required' => false,
                'attr' => ['min' => 0, 'max' => 100]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver.setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
