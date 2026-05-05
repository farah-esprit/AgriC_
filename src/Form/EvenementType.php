<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Evenement>
 */
class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'événement',
                'attr'  => ['class' => 'form-control', 'placeholder' => 'Entrez le titre'],
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'attr'     => ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Détails de l\'événement'],
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr'  => ['class' => 'form-control js-flatpickr-time', 'placeholder' => 'Sélectionnez une date'],
            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr'  => ['class' => 'form-control js-flatpickr-time', 'placeholder' => 'Sélectionnez une date'],
            ])
            ->add('lieu', TextType::class, [
                'label'    => 'Lieu',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'Où se déroule l\'événement ?'],
            ])
            ->add('capaciteMax', IntegerType::class, [
                'label'    => 'Capacité maximale',
                'required' => false,
                'attr'     => ['class' => 'form-control', 'placeholder' => 'Nombre de places'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}