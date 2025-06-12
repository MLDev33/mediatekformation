<?php

namespace App\Controller\admin;

use App\Entity\Categorie;
use App\Form\CategorieTypeForm;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contrôleur de gestion des catégories côté administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
class AdminCategoriesController extends AbstractController
{

    /**
     * Repository des formations.
     *
     * @var FormationRepository
     */
    private $formationRepository;

    /**
     * Repository des catégories.
     *
     * @var CategorieRepository
     */
    private $categorieRepository;

    /**
     * Chemin du template pour l'affichage des catégories.
     */
    private const TEMPLATE_LIST_CATEGORIES = "admin/categories/admin.categories.html.twig";

    /**
     * Constructeur du contrôleur des catégories.
     *
     * Initialise les repositories nécessaires pour la gestion des catégories et des formations.
     *
     * @param CategorieRepository $categorieRepository  Repository des catégories
     * @param FormationRepository $formationRespository Repository des formations
     */
    public function __construct(
        CategorieRepository $categorieRepository,
        FormationRepository $formationRespository
    )
    {
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRespository;
    }

    /**
     * Crée un formulaire d’ajout de catégorie avec les options POST prédéfinies.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createCategorieForm()
    {
        return $this->createForm(CategorieTypeForm::class, new Categorie(), [
                'action' => $this->generateUrl('admin.categorie.ajouter'),
                'method' => 'POST',
        ]);
    }

    /**
     * Affiche la liste des catégories avec le formulaire d'ajout.
     *
     * Traite la soumission du formulaire si elle a lieu.
     *
     * @param Request $request  Requête contenant éventuellement les données du formulaire soumis
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/categories', name: 'admin.categories')]
    public function index(Request $request): Response
    {
        $categories = $this->categorieRepository->findAllOrderByName('ASC');

        $formCategorie = $this->createCategorieForm();

        $formCategorie->handleRequest($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {
            $categorie = $formCategorie->getData();
            $this->categorieRepository->add($categorie);
            $this->addFlash('success', "Catégorie  \"{$categorie->getName()}\" ajoutée.");
            return $this->redirectToRoute('admin.categories');
        }

        return $this->render(self::TEMPLATE_LIST_CATEGORIES, [
                'categories' => $categories,
                'formcategorie' => $formCategorie->createView()
        ]);
    }

    /**
     * Trie dynamiquement les catégories selon le champ et l’ordre spécifiés.
     *
     * Permet de trier par nom ou par nombre de formations.
     *
     * @param string $champ Champ à trier (ex. : "name", "nbFormations")
     * @param string $ordre Ordre de tri (ex. : "ASC" ou "DESC")
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/categories/tri/{champ}/{ordre}', name: 'admin.categories.sort')]
    public function sort(string $champ, string $ordre): Response
    {
        $formCategorie = $this->createCategorieForm();

        if ($champ === 'name') {
            $categories = $this->categorieRepository->findAllOrderByName($ordre);
        } elseif ($champ === 'nbFormations') {
            $categories = $this->categorieRepository->findAllOrderByFormationsCount($ordre);
        } else {
            $categories = [];
        }

        return $this->render(self::TEMPLATE_LIST_CATEGORIES, [
                'categories' => $categories,
                'formcategorie' => $formCategorie->createView()
        ]);
    }

    /**
     * Recherche les catégories contenant une valeur dans un champ spécifique,
     * avec option de filtrage par une table associée.
     *
     * Utilise les paramètres dynamiques pour effectuer une recherche souple.
     *
     * @param string $champ Champ sur lequel effectuer la recherche (ex. : "name")
     * @param Request $request  Requête contenant le champ "recherche"
     * @param string $table Nom de la table associée pour filtrage avancé (optionnel)
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/categories/recherche/{champ}/{table}', name: 'admin.categories.findallcontain')]
    public function findAllContain(string $champ, Request $request, string $table = ""): Response
    {
        $formCategorie = $this->createCategorieForm();

        $valeur = $request->get("recherche");
        $categories = $this->categorieRepository->findByContainValue($champ, $valeur, $table);

        return $this->render(self::TEMPLATE_LIST_CATEGORIES, [
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table,
                'formcategorie' => $formCategorie->createView()
        ]);
    }

    /**
     * Supprime une catégorie si elle ne contient aucune formation.
     *
     * Vérifie le token CSRF et l'existence de la catégorie.
     *
     * @param int $id   Identifiant de la catégorie à supprimer
     * @param Request $request Requête contenant le token CSRF
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/categories/supprimer/{id<\d+>}', name: 'admin.categorie.supprimer')]
    public function supprimer(int $id, Request $request): Response
    {
        $categorie = $this->categorieRepository->find($id);

        if (!$categorie) {
            $this->addFlash('error', 'Catégorie introuvable');
            return $this->redirectToRoute('admin.categories');
        }

        if (!$this->isCsrfTokenValid('supprimer_categorie_' . $categorie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin.categories');
        }

        $formationsCount = $categorie->getFormations()->count();

        if ($formationsCount > 0) {
            $this->addFlash('warning', "La catégorie \"{$categorie->getName()}\" contient $formationsCount formations et ne peut pas être supprimée.");
            return $this->redirectToRoute('admin.categories');
        }

        $this->categorieRepository->remove($categorie);
        $this->addFlash('success', "Catégorie \"{$categorie->getName()}\" supprimée.");

        return $this->redirectToRoute('admin.categories');
    }

    /**
     * Ajoute une nouvelle catégorie après validation du formulaire.
     *
     * Vérifie que le nom n'est pas vide et qu’il n’existe pas déjà.
     *
     * @param Request $request Requête contenant les données du formulaire soumis
     * @return Response Réponse HTTP avec le rendu de la page
     */
    #[Route('/admin/categorie/ajouter', name: 'admin.categorie.ajouter')]
    public function ajouter(Request $request): Response
    {
        $formCategorie = $this->createCategorieForm();
        $formCategorie->handleRequest($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {
            $categorie = $formCategorie->getData();
            $name = trim($categorie->getName());

            if ($name === '') {
                $this->addFlash('warning', 'Le nom de la catégorie ne peut pas être vide.');
                return $this->redirectToRoute('admin.categories');
            }

            $categorieExistante = $this->categorieRepository->findOneByName($name);

            if ($categorieExistante) {
                $this->addFlash('warning', "La catégorie \"$name\" existe déjà.");
                return $this->redirectToRoute('admin.categories');
            }

            $this->categorieRepository->add($categorie);
            $this->addFlash('success', "Catégorie  \"{$categorie->getName()}\" ajoutée.");
            return $this->redirectToRoute('admin.categories');
        }

        return $this->redirectToRoute('admin.categories');
    }
}
