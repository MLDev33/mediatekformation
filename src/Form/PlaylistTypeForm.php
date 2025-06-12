<?php

namespace App\Form;

use App\Entity\Playlist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Formulaire pour la gestion des playlists.
 *
 * Définit la structure et les options du formulaire pour créer ou éditer une entité Playlist.
 *
 * @package App\Form
 */
class PlaylistTypeForm extends AbstractType
{

    /**
     * Construction du formulaire.
     *
     * Ajoute les champs suivants :
     * - name : nom de la playlist (texte obligatoire)
     * - description : description de la playlist (texte optionnel)
     * - submit : bouton d'enregistrement
     *
     * @param FormBuilderInterface $builder Constructeur du formulaire.
     * @param array $options Options passées lors de la création du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom',
                'required' => true
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
        ;
    }

    /**
     * Configuration des options du formulaire.
     *
     * Lie ce formulaire à la classe de données Playlist pour la transformation automatique.
     *
     * @param OptionsResolver $resolver Résolveur d'options du formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Playlist::class,
        ]);
    }
}
