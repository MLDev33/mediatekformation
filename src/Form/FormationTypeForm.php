<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Formation;
use App\Entity\Playlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use DateTime;

/**
 * Formulaire pour la gestion des formations.
 *
 * Définit la structure et les options du formulaire pour créer ou éditer une entité Formation.
 *
 * @package App\Form
 */
class FormationTypeForm extends AbstractType
{

    /**
     * Construction du formulaire.
     *
     * Ajoute les champs suivants :
     * - publishedAt : date de parution (champ date avec contraintes sur la date max)
     * - title : titre de la formation (texte obligatoire)
     * - description : description de la formation (texte optionnel)
     * - videoId : identifiant de la vidéo (texte obligatoire)
     * - playlist : association à une Playlist (entité liée, choix unique)
     * - categories : association à une ou plusieurs catégories (entités liées, choix multiple)
     * - submit : bouton d'enregistrement
     *
     * @param FormBuilderInterface $builder Constructeur du formulaire.
     * @param array $options Options passées lors de la création du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('publishedAt', DateType::class, [
                'required' => true,
                'widget' => 'single_text',
                'data' => isset($options['data']) &&
                $options['data']->getPublishedAt() != null ? $options['data']->getPublishedAt() : new DateTime('now'),
                'label' => 'Date de parution',
                'html5' => true,
                'attr' => [
                    'max' => (new \DateTime())->format('d-m-Y'),
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => "La date ne peut pas être postérieure à aujourd'hui."
                        ]),
                ],
            ])
            ->add('title', null, [
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('description', null, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('videoId', null, [
                'required' => true,
                'label' => 'Identifiant de la vidéo'
            ])
            ->add('playlist', EntityType::class, [
                'class' => Playlist::class,
                'choice_label' => 'name',
                'required' => true,
            ])
            ->add('categories', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'multiple' => true,
                'label' => 'Catégorie',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
        ;
    }

    /**
     * Configuration des options du formulaire.
     *
     * Lie ce formulaire à la classe de données Formation pour la transformation automatique.
     *
     * @param OptionsResolver $resolver Résolveur d'options du formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
