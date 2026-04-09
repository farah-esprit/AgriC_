<?php

namespace App\Form;

use App\Entity\Culture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CultureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la culture',
                'attr' => ['placeholder' => 'Ex: Blé dur']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de culture',
                'choices' => [
                    'Céréales' => 'Céréales',
                    'Légumes' => 'Légumes',
                    'Fruits' => 'Fruits',
                    'Oléagineux' => 'Oléagineux',
                    'Fourragères' => 'Fourragères',
                    'Autre' => 'Autre'
                ]
            ])
            ->add('superficie', NumberType::class, [
                'label' => 'Superficie (hectares)',
                'attr' => ['step' => 0.1]
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation/Région',
                'attr' => ['placeholder' => 'Ex: Béja']
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
