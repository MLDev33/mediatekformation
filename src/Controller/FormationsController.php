<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur gérant l'affichage des formations et des catégories associées.
 *
 * Fournit les actions pour lister, trier, rechercher et afficher une formation.
 * Utilise les repositories FormationRepository et CategorieRepository.
 *
 */
class FormationsController extends AbstractController
{

    /**
     * Repository pour accéder aux données des formations.
     *
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     * Repository pour accéder aux catégories des formations.
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    /**
     * Template d'affichage de la liste des formations.
     */
    private const TEMPLATE_LIST_FORMATIONS = "pages/formations.html.twig";

    /**
     * Template d'affichage du détail d'une formation.
     */
    private const TEMPLATE_DETAIL_FORMATION = "pages/formation.html.twig";

    /**
     * Constructeur du contrôleur des formations.
     *
     * Initialise les repositories nécessaires pour l'administration des formations.
     *
     * @param FormationRepository $formationRepository Repository des formations
     * @param CategorieRepository $categorieRepository Repository des catégories
     */
    public function __construct(FormationRepository $formationRepository, CategorieRepository $categorieRepository)
    {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Affiche la liste de toutes les formations.
     *
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/formations', name: 'formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::TEMPLATE_LIST_FORMATIONS, [
                'formations' => $formations,
                'categories' => $categories
        ]);
    }

    /**
     * Trie les formations selon un champ, un ordre et éventuellement une table liée.
     *
     * @param string $champ   Champ à trier (ex. : "title", "date")
     * @param string $ordre   Ordre de tri (ex. : "ASC" ou "DESC")
     * @param string $table   Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/formations/tri/{champ}/{ordre}/{table}', name: 'formations.sort')]
    public function sort(string $champ, string $ordre, string $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::TEMPLATE_LIST_FORMATIONS, [
                'formations' => $formations,
                'categories' => $categories
        ]);
    }

    /**
     * Recherche des formations contenant une valeur dans un champ donné.
     *
     * @param string $champ   Champ sur lequel effectuer la recherche (ex. : "name", "nbFormations")
     * @param Request $request  Requête contenant le champ "recherche"
     * @param string $table     Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/formations/recherche/{champ}/{table}', name: 'formations.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::TEMPLATE_LIST_FORMATIONS, [
                'formations' => $formations,
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    /**
     *  Affiche le détail d'une formation selon l'id donné
     *
     * @param int $id   Identifiant de la formation à afficher
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/formations/{id<\d+>}', name: 'formations.showone')]
    public function showOne(int $id): Response
    {
        $formation = $this->formationRepository->find($id);
        return $this->render(self::TEMPLATE_DETAIL_FORMATION, [
                'formation' => $formation
        ]);
    }
}
