<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Reclamation>
 */
class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objet', TextType::class, [
                'label' => 'Objet',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('dateCreation', \Symfony\Component\Form\Extension\Core\Type\DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'En cours' => 'En cours',
                    'Résolu' => 'Résolu',
                    'Fermé' => 'Fermé',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Faible' => 'Faible',
                    'Moyenne' => 'Moyenne',
                    'Élevée' => 'Élevée',
                    'Urgente' => 'Urgente',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Problème technique' => 'Problème technique',
                    'Question' => 'Question',
                    'Suggestion' => 'Suggestion',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('reponseAdmin', TextareaType::class, [
                'label' => 'Réponse de l\'administrateur',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('dateReponse', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => 'Date de réponse',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}