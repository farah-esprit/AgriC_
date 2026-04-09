<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateCommande', TextType::class, [
                'label' => 'Date de la commande',
                'attr' => ['placeholder' => 'YYYY-MM-DD']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'En attente',
                    'Payée' => 'Payée',
                    'Expédiée' => 'Expédiée',
                    'Livrée' => 'Livrée',
                    'Annulée' => 'Annulée'
                ]
            ])
            ->add('quantiteCommandee', IntegerType::class, [
                'label' => 'Quantité'
            ])
            ->add('prixTotal', NumberType::class, [
                'label' => 'Prix Total (TND)'
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'label' => 'Produit'
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'label' => 'Client'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
