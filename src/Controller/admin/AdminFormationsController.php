<?php

namespace App\Controller\admin;

use App\Entity\Formation;
use App\Form\FormationTypeForm;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur de gestion des formations côté administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
class AdminFormationsController extends AbstractController
{

    /**
     * Repository des formations.
     *
     * @var FormationRepository
     */
    private FormationRepository $formationRepository;

    /**
     *  Repository des catégories.
     *
     * @var CategorieRepository
     */
    private CategorieRepository $categorieRepository;

    /**
     * Template d'affichage de la liste des formations.
     */
    private const TEMPLATE_LIST_FORMATIONS = "admin/formations/admin.formations.html.twig";

    /**
     * Template d'affichage du détail d'une formation.
     */
    private const TEMPLATE_DETAIL_FORMATION = "admin/formations/admin.formation.html.twig";

    /**
     * Template pour l'édition d'une formation existante.
     */
    private const TEMPLATE_EDIT_FORMATION = "admin/formations/admin.formation.edit.html.twig";

    /**
     * Template pour l'ajout d'une nouvelle formation.
     */
    private const TEMPLATE_ADD_FORMATION = 'admin/formations/admin.formation.add.html.twig';

    /**
     * Constructeur du contrôleur qui administre les formations.
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
     * Méthode privée pour centraliser le rendu de la liste des formations.
     *
     * @param array $formations Liste des formations à afficher
     * @param string|null $valeur   Valeur de recherche (optionnel)
     * @param string $table Nom de la table associée (optionnel)
     */
    private function renderListFormations(array $formations, ?string $valeur = null, string $table = ''): Response
    {
        return $this->render(self::TEMPLATE_LIST_FORMATIONS, [
                'formations' => $formations,
                'categories' => $this->categorieRepository->findAll(),
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    /**
     * Affiche la liste de toutes les formations.
     *
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formations', name: 'admin.formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();

        return $this->renderListFormations($formations);
    }

    /**
     * Trie les formations selon un champ, un ordre et éventuellement une table liée.
     *
     * @param string $champ   Champ à trier (ex. : "title", "date")
     * @param string $ordre   Ordre de tri (ex. : "ASC" ou "DESC")
     * @param string $table   Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formations/tri/{champ}/{ordre}/{table}', name: 'admin.formations.sort')]
    public function sort(string $champ, string $ordre, string $table = ''): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);

        return $this->renderListFormations($formations);
    }

    /**
     * Recherche des formations contenant une valeur dans un champ donné.
     *
     * @param string $champ   Champ sur lequel effectuer la recherche (ex. : "name", "nbFormations")
     * @param Request $request  Requête contenant le champ "recherche"
     * @param string $table     Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formations/recherche/{champ}/{table}', name: 'admin.formations.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);

        return $this->renderListFormations($formations);
    }

    /**
     *  Affiche le détail d'une formation selon l'id donné
     *
     * @param int $id   Identifiant de la formation à afficher
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formation/{id<\d+>}', name: 'admin.formations.showone')]
    public function showOne(int $id): Response
    {
        $formation = $this->formationRepository->find($id);

        if (!$formation) {
            $this->addFlash('error', "Formation non trouvée.");
            return $this->redirectToRoute('admin.formations');
        }

        // Renvoie vers la page qui affiche une formation
        return $this->render(self::TEMPLATE_DETAIL_FORMATION, [
                'formation' => $formation,
        ]);
    }

    /**
     * Supprime une formation après validation du token CSRF.
     *
     * @param int $id Identifiant de la formation à supprimer
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formation/supprimer/{id<\d+>}', name: 'admin.formation.supprimer')]
    public function supprimer(int $id, Request $request): Response
    {
        $formation = $this->formationRepository->find($id);

        if (!$formation) {
            $this->addFlash('error', "Formation non trouvée.");
            return $this->redirectToRoute('admin.formations');
        }

        if ($this->isCsrfTokenValid('delete' . $formation->getId(), $request->get('_token'))) {
            $this->formationRepository->remove($formation);
        }

        return $this->redirectToRoute('admin.formations');
    }

    /**
     * Modifie une formation existante via un formulaire.
     *
     * @param int $id   Identifiant de la formation à modifier
     * @param Request $request  La requête contenant les données du formulaire.
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formation/modifier/{id<\d+>}', name: 'admin.formation.modifier')]
    public function modifier(int $id, Request $request): Response
    {
        $formation = $this->formationRepository->find($id);

        if (!$formation) {
            $this->addFlash('error', "Formation non trouvée.");
            return $this->redirectToRoute('admin.formations');
        }

        $formFormation = $this->createForm(FormationTypeForm::class, $formation);
        $formFormation->handleRequest($request);

        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->formationRepository->add($formation);
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render(self::TEMPLATE_EDIT_FORMATION, [
                'formation' => $formation,
                'formformation' => $formFormation->createView()
        ]);
    }

    /**
     * Ajoute une nouvelle formation via un formulaire.
     *
     * @param Request $request  Requête HTTP avec les données du formulaire
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/formation/ajouter', name: 'admin.formation.ajouter')]
    public function ajouter(Request $request): Response
    {
        $formation = new Formation();
        $formFormation = $this->createForm(FormationTypeForm::class, $formation);
        $formFormation->handleRequest($request);

        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->formationRepository->add($formation);
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render(self::TEMPLATE_ADD_FORMATION, [
                'formation' => $formation,
                'formformation' => $formFormation->createView()
        ]);
    }
}
