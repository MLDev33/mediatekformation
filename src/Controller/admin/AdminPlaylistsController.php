<?php

namespace App\Controller\admin;

use App\Entity\Playlist;
use App\Form\PlaylistTypeForm;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of AdminPlaylistsController
 *
 * @author m-lordiportable
 */
class AdminPlaylistsController extends AbstractController
{

    /**
     *
     * @var PlaylistRepository
     */
    private $playlistRepository;

    /**
     *
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    /**
     * Début de chemin vers les playlists
     */
    private const CHEMINPLAYLISTS = "admin/playlists/admin.playlists.html.twig";

    /**
     * Chemin du template pour l'affichage d'une seule playlist
     */
    private const CHEMINPLAYLIST = "admin/playlists/admin.playlist.html.twig";

    public function __construct(
        PlaylistRepository $playlistRepository,
        CategorieRepository $categorieRepository,
        FormationRepository $formationRespository
    )
    {
        $this->playlistRepository = $playlistRepository;
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRespository;
    }

    /**
     * @Route("/playlists", name="playlists")
     * @return Response
     */
    #[Route('/admin/playlists', name: 'admin.playlists')]
    public function index(): Response
    {
        $playlists = $this->playlistRepository->findAllOrderByName('ASC');
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINPLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories,
        ]);
    }

    #[Route('/admin/playlists/tri/{champ}/{ordre'
            . '}', name: 'admin.playlists.sort')]
    public function sort($champ, $ordre): Response
    {

        if ($champ === 'name') {
            $playlists = $this->playlistRepository->findAllOrderByName($ordre);
        }
        if ($champ === 'nbFormations') {
            $playlists = $this->playlistRepository->findAllOrderByFormationsCount($ordre);
        }
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINPLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories
        ]);
    }

    #[Route('/admin/playlists/recherche/{champ}/{table}', name: 'admin.playlists.findallcontain')]
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $playlists = $this->playlistRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINPLAYLISTS, [
                'playlists' => $playlists,
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    #[Route('/admin/playlists/{id<\d+>}', name: 'admin.playlists.showone')]
    public function showOne($id): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        return $this->render(self::CHEMINPLAYLIST, [
                'playlist' => $playlist,
                'playlistcategories' => $playlistCategories,
                'playlistformations' => $playlistFormations
        ]);
    }

    /**
     * Supprime une playlists.
     *
     * @param type $id
     * @param Request $request
     * @return Response
     * @throws type
     */
    #[Route('/admin/playlist/supprimer/{id<\d+>}', name: 'admin.playlist.supprimer')]
    public function supprimer($id, Request $request): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $formationsCount = $playlist->getFormations()->count();

        if (!$playlist) {
            throw $this->createNotFoundException('Playlist introuvable');
        }

        if (!$this->isCsrfTokenValid('supprimer_playlist_' . $playlist->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin.playlists');
        }

        if ($formationsCount > 0) {
            $this->addFlash('warning', "La playlist \"{$playlist->getName()}\" contient $formationsCount formations et ne peut pas être supprimée.");
            return $this->redirectToRoute('admin.playlists');
        }

        $this->playlistRepository->remove($playlist);
        $this->addFlash('success', "Playlist \"{$playlist->getName()}\" supprimée.");

        return $this->redirectToRoute('admin.playlists');
    }

    /**
     * Modifie une formation existante.
     *
     * @param type $id
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/playlist/modifier/{id<\d+>}', name: 'admin.playlist.modifier')]
    public function modifier($id, Request $request): Response
    {
        $playlist = $this->playlistRepository->find($id);
        $playlistCategories = $this->categorieRepository->findAllForOnePlaylist($id);
        $playlistFormations = $this->formationRepository->findAllForOnePlaylist($id);
        $formPlaylist = $this->createForm(PlaylistTypeForm::class, $playlist);

        $formPlaylist->handleRequest($request);
        if ($formPlaylist->isSubmitted() && $formPlaylist->isValid()) {
            $this->playlistRepository->add($playlist);
            return $this->redirectToRoute('admin.playlists');
        }

        return $this->render("admin/playlists/admin.playlist.edit.html.twig", [
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

        return $this->render("admin/playlists/admin.playlist.add.html.twig", [
                'playlist' => $playlist,
                'formplaylist' => $formPlaylist->createView()
        ]);
    }
}
