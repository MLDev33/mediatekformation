<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la gestion des catégories.
 *
 * Définit la structure et les options du formulaire pour créer ou éditer une entité Categorie.
 *
 * @package App\Form
 */
class CategorieTypeForm extends AbstractType
{

    /**
     * Construction du formulaire.
     *
     * Ajoute un champ 'name' de type texte avec label et contrainte de saisie obligatoire.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire Symfony.
     * @param array $options Options passées lors de la création du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nouvelle catégorie',
                'required' => true
            ])
        ;
    }

    /**
     * Configuration des options du formulaire.
     *
     * Lie ce formulaire à la classe de données Categorie pour la transformation automatique.
     *
     * @param OptionsResolver $resolver Résolveur d'options du formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
