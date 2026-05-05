<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Stock;
use App\Repository\ProduitRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * @extends AbstractType<Stock>
 */
class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité totale',
                'attr'  => ['min' => 0, 'placeholder' => 'Ex: 100'],
                'constraints' => [
                    new NotBlank(message: 'La quantité totale est obligatoire.'),
                    new PositiveOrZero(message: 'La quantité totale doit être positive ou nulle.'),
                ],
            ])
            ->add('disponible', IntegerType::class, [
                'label' => 'Quantité disponible',
                'attr'  => ['min' => 0, 'placeholder' => 'Ex: 80'],
                'constraints' => [
                    new NotBlank(message: 'La quantité disponible est obligatoire.'),
                    new PositiveOrZero(message: 'La quantité disponible doit être positive ou nulle.'),
                ],
            ])
            ->add('seuilAlert', IntegerType::class, [
                'label' => "Seuil d'alerte",
                'attr'  => ['min' => 0, 'placeholder' => 'Ex: 10'],
                'constraints' => [
                    new NotBlank(message: "Le seuil d'alerte est obligatoire."),
                    new PositiveOrZero(message: "Le seuil d'alerte doit être positif ou nul."),
                ],
            ])
            ->add('produit', EntityType::class, [
                'class'        => Produit::class,
                'choice_label' => 'nom',
                'label'        => 'Produit associé',
                'placeholder'  => '-- Choisir un produit --',
                'disabled'     => $isEdit,
                'query_builder' => function (ProduitRepository $repo) use ($isEdit) {
                    $qb = $repo->createQueryBuilder('p')
                        ->leftJoin('p.stock', 's')
                        ->orderBy('p.nom', 'ASC');

                    // En mode création : exclure les produits qui ont déjà un stock
                    if (!$isEdit) {
                        $qb->where('s.idStock IS NULL');
                    }

                    return $qb;
                },
                'constraints'  => [
                    new NotNull(message: 'Veuillez sélectionner un produit.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
            'is_edit'    => false,
        ]);
    }
}