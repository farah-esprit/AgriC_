<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

/**
 * @extends AbstractType<Commande>
 */
class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $today = (new \DateTime())->format('Y-m-d');

        $builder
            ->add('dateCommande', TextType::class, [
                'label' => 'Date de la commande',
                'attr'  => [
                    'placeholder' => 'Cliquez pour choisir une date...',
                    'readonly'    => 'readonly',
                    'style'       => 'cursor:pointer;',
                ],
                'constraints' => [
                    new NotBlank(message: 'La date de commande est obligatoire.'),
                    new GreaterThanOrEqual(
                        value: $today,
                        message: 'La date de commande ne peut pas être dans le passé.'
                    ),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label'       => 'Statut',
                'placeholder' => '-- Choisir un statut --',
                'choices'     => [
                    'En attente' => 'EN_ATTENTE',
                    'Validée'    => 'VALIDEE',
                    'Payée'      => 'PAYEE',
                    'Expédiée'   => 'EXPEDIEE',
                    'Livrée'     => 'LIVREE',
                    'Annulée'    => 'ANNULEE',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le statut est obligatoire.'),
                ],
            ])
            ->add('quantiteCommandee', IntegerType::class, [
                'label' => 'Quantité',
                'attr'  => [
                    'min'         => 1,
                    'placeholder' => 'Ex: 2',
                    'onkeypress'  => 'return event.charCode >= 48 && event.charCode <= 57',
                ],
                'constraints' => [
                    new NotBlank(message: 'La quantité est obligatoire.'),
                    new Positive(message: 'La quantité doit être au moins 1.'),
                    new LessThanOrEqual(
                        value: 9999,
                        message: 'La quantité ne peut pas dépasser {{ compared_value }}.'
                    ),
                ],
            ])
            ->add('produit', EntityType::class, [
                'class'        => Produit::class,
                'choice_label' => 'nom',
                'label'        => 'Produit',
                'placeholder'  => '-- Choisir un produit --',
                'constraints'  => [
                    new NotNull(message: 'Veuillez sélectionner un produit.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}