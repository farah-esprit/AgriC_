<?php

namespace App\Form;

use App\Entity\Diagnostic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiagnosticType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDiagnostic', TextType::class, [
                'label' => 'Date du diagnostic',
                'attr' => ['placeholder' => 'YYYY-MM-DD']
            ])
            ->add('symptomes', TextareaType::class, [
                'label' => 'Symptômes observés',
                'attr' => ['rows' => 4]
            ])
            ->add('informationsComplementaires', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'required' => false,
                'attr' => ['rows' => 4]
            ])
            ->add('idCulture', IntegerType::class, [
                'label' => 'ID de la culture concernée'
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
