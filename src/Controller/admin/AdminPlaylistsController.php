<?php

namespace App\Controller\admin;

use App\Entity\Playlist;
use App\Form\PlaylistTypeForm;
use App\Repository\PlaylistRepository;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur de gestion des playlists côté administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
class AdminPlaylistsController extends AbstractController
{

    /**
     * Repository pour accéder aux données des playlists.
     *
     * @var PlaylistRepository
     */
    private $playlistRepository;

    /**
     * Repository pour accéder aux formations.
     *
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     * Repository pour accéder aux catégories.
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    /**
     * Template d'affichage de la liste des playlists.
     */
    private const TEMPLATE_LIST_PLAYLISTS = "admin/playlists"
        . "/admin.playlists.html.twig";

    /**
     * Template d'affichage du détail d'une playlist.
     */
    private const TEMPLATE_DETAIL_PLAYLIST = "admin/playlists/admin.playlist.html.twig";

    /**
     * Template pour l'édition d'une playlist existante.
     */
    private const TEMPLATE_EDIT_PLAYLIST = "admin/playlists/admin.playlist.edit.html.twig";

    /**
     * Template pour l'ajout d'une nouvelle playlist.
     */
    private const TEMPLATE_ADD_PLAYLIST = 'admin/playlists/admin.playlist.add.html.twig';

    /**
     * Constructeur du contrôleur qui administre les playlists.
     *
     * Initialise les repositories nécessaires pour l'administration des playlists.
     *
     * @param PlaylistRepository $playlistRepository Repository des playlists
     * @param CategorieRepository $categorieRepository Repository des catégories
     * @param FormationRepository $formationRepository Repository des formations
     */
    public function __construct(
        PlaylistRepository $playlistRepository,
        CategorieRepository $categorieRepository,
        FormationRepository $formationRepository
    )
    {
        $this->playlistRepository = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRepository;
    }

    /**
     * Méthode privée pour centraliser le rendu de la liste des playlists.
     *
     * @param array $playlists Liste des playlists à afficher
     * @param string $valeur Valeur de recherche (optionnel)
     * @param string $table Nom de la table associée (optionnel)
     * @return Response HTTP avec le rendu de la page
     */
    private function renderListPlaylists(array $playlists, string $valeur = '', string $table = ''): Response
    {
        return $this->render(self::TEMPLATE_LIST_PLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $this->categorieRepository->findAll(),
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    /**
     * Affiche la liste de toutes les playlists.
     *
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/playlists', name: 'admin.playlists')]
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');

        return $this->renderListPlaylists($playlists);
    }

    /**
     * Trie les playlists selon un champ donné (un nom ou nombre de formations)
     *
     * @param string $champ Le champ de tri (name ou nbFormations)
     * @param string $ordre L'ordre de tri (ASC ou DESC)
     * @return Response HTTP avec le rendu de la page
     */
    #[Route('/admin/playlists/tri/{champ}/{ordre}', name: 'admin.playlists.sort')]
    public function sort(string $champ, string $ordre): Response
    {

        if ($champ === 'name') {
            $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        } elseif ($champ === 'nbFormations') {
            $playlists = $this->playlistRepository->findAllOrderByFormationsCount($ordre);
        } else {
            $playlists = [];
        }


        return $this->renderListPlaylists($playlists);
    }

    /**
     * Recherche des playlists contenant une valeur dans un champ donné.
     *
     * @param string $champ   Champ sur lequel effectuer la recherche (ex. : "name")
     * @param Request $request  Requête contenant le champ "recherche"
     * @param string $table     Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/playlists/recherche/{champ}/{table}', name: 'admin.playlists.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);

        return $this->renderListPlaylists($playlists, $valeur, $table);
    }

    /**
     *  Affiche le détail d'une playlist selon l'id donné
     *
     * @param int $id   Identifiant de la playlist à afficher
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/playlists/{id<\d+>}', name: 'admin.playlists.showone')]
    public function showOne(int $id): Response
    {
        $playlist = $this->playlistRepository->find($id);

        if (!$playlist) {
            $this->addFlash('error', 'Playlist non trouvée.');
            return $this->redirectToRoute('admin.playlists');
        }

        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);

        return $this->render(self::TEMPLATE_DETAIL_PLAYLIST, [
                'playlist' => $playlist,
                'playlistcategories' => $playlistCategories,
                'playlistformations' => $playlistFormations
        ]);
    }

    /**
     *  Supprime une playlist si elle ne contient aucune formation.
     *
     * @param int $id   Identifiant de la playlist à supprimer
     * @param Request $request  La requête contenant les données du formulaire.
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/playlist/supprimer/{id<\d+>}', name: 'admin.playlist.supprimer')]
    public function supprimer(int $id, Request $request): Response
    {
        $playlist = $this->playlistRepository->find($id);

        if (!$playlist) {
            $this->addFlash('error', 'Playlist non trouvée');
            return $this->redirectToRoute('admin.playlists');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('supprimer_playlist_' . $playlist->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin.playlists');
        }

        $formationsCount = $playlist->getFormations()->count();

        if ($formationsCount > 0) {
            $this->addFlash('warning', "La playlist \"{$playlist->getName()}\" contient $formationsCount formations et ne peut pas être supprimée.");
            return $this->redirectToRoute('admin.playlists');
        }

        $this->playlistRepository->remove($playlist);
        $this->addFlash('success', "Playlist \"{$playlist->getName()}\" supprimée.");

        return $this->redirectToRoute('admin.playlists');
    }

    /**
     * Modifie une playlist existante via un formulaire.
     *
     * @param int $id Identifiant de la playlist à modifier
     * @param Request $request
     * @return Response HTTP avec le rendu de la page
     */
    #[Route('/admin/playlist/modifier/{id<\d+>}', name: 'admin.playlist.modifier')]
    public function modifier(int $id, Request $request): Response
    {
        $playlist = $this->playlistRepository->find($id);

        if (!$playlist) {
            $this->addFlash('error', 'Playlist non trouvée');
            return $this->redirectToRoute('admin.playlists');
        }

        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        $formPlaylist = $this->createForm(PlaylistTypeForm::class, $playlist);

        $formPlaylist->handleRequest($request);
        if ($formPlaylist->isSubmitted() && $formPlaylist->isValid()) {
            $this->playlistRepository->add($playlist);
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render(self::TEMPLATE_EDIT_PLAYLIST, [
                'playlist' => $playlist,
                'playlistcategories' => $playlistCategories,
                'playlistformations' => $playlistFormations,
                'formplaylist' => $formPlaylist->createView()
        ]);
    }

    /**
     * Ajoute une nouvelle playlist.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/playlist/ajouter', name: 'admin.playlist.ajouter')]
    public function ajouter(Request $request): Response
    {
        $playlist = new Playlist();
        $formPlaylist = $this->createForm(PlaylistTypeForm::class, $playlist);

        $formPlaylist->handleRequest($request);
        if ($formPlaylist->isSubmitted() && $formPlaylist->isValid()) {
            $this->playlistRepository->add($playlist);
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render(self::TEMPLATE_ADD_PLAYLIST, [
                'playlist' => $playlist,
                'formplaylist' => $formPlaylist->createView()
        ]);
    }
}
