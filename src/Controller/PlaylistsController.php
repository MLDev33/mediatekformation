<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur gérant les playlists, leurs catégories et formations associées.
 */
class PlaylistsController extends AbstractController
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
    private const TEMPLATE_LIST_PLAYLISTS = "pages/playlists.html.twig";

    /**
     * Constructeur du contrôleur Playlists.
     *
     * @param PlaylistRepository $playlistRepository
     * @param CategorieRepository $categorieRepository
     * @param FormationRepository $formationRepository
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
     * Affiche la liste de toutes les playlists.
     *
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/playlists', name: 'playlists')]
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::TEMPLATE_LIST_PLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories,
        ]);
    }

    /**
     * Trie les playlists selon un champ donné (un nom ou nombre de formations)
     *
     * @param string $champ Le champ de tri (name ou nbFormations)
     * @param string $ordre L'ordre de tri (ASC ou DESC)
     * @return Response HTTP avec le rendu de la page
     */
    #[Route('/playlists/tri/{champ}/{ordre'
            . '}', name: 'playlists.sort')]
    public function sort(string $champ, string $ordre): Response
    {

        if ($champ === 'name') {
            $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        } elseif ($champ === 'nbFormations') {
            $playlists = $this->playlistRepository->findAllOrderByFormationsCount($ordre);
        } else {
            $playlists = [];
        }

        $categories = $this->categorieRepository->findAll();

        return $this->render(self::TEMPLATE_LIST_PLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories
        ]);
    }

    /**
     * Recherche des playlists contenant une valeur dans un champ donné.
     *
     * @param string $champ   Champ sur lequel effectuer la recherche (ex. : "name")
     * @param Request $request  Requête contenant le champ "recherche"
     * @param string $table     Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/playlists/recherche/{champ}/{table}', name: 'playlists.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $valeur = $request->get("recherche");

        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();

        return $this->render(self::TEMPLATE_LIST_PLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    /**
     * Affiche le détail d'une playlist avec ses catégories et formations.
     *
     * @param int $id Identifiant de la playlist
     * @return Response  HTTP avec le rendu de la page
     * @throws NotFoundHttpException Si la playlist n'est pas trouvée
     */
    #[Route('/playlists/playlist/{id<\d+>}', name: 'playlists.showone')]
    public function showOne(int $id): Response
    {
        $playlist = $this->playlistRepository->find($id);

        if (!$playlist) {
            throw $this->createNotFoundException('Playlist non trouvée.');
        }

        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);

        return $this->render("pages/playlist.html.twig", [
                'playlist' => $playlist,
                'playlistcategories' => $playlistCategories,
                'playlistformations' => $playlistFormations
        ]);
    }
}
