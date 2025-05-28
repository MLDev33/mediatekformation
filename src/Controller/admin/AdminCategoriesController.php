<?php

namespace App\Controller\admin;

use App\Entity\Categorie;
use App\Form\CategorieTypeForm;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of AdminCategoriesController
 *
 * @author m-lordiportable
 */
class AdminCategoriesController extends AbstractController
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
    private const CHEMINCATEGORIES = "admin/categories/admin.categories.html.twig";

    public function __construct(
        CategorieRepository $categorieRepository,
        FormationRepository $formationRespository
    )
    {
        $this->categorieRepository = $categorieRepository;
        $this->formationRepository = $formationRespository;
    }

    /**
     * @Route("/categories", name="categories")
     * @return Response
     */
    #[Route('/admin/categories', name: 'admin.categories')]
    public function index(Request $request): Response
    {
        $categories = $this->categorieRepository->findAllOrderByName('ASC');

        $categorie = new Categorie();
        $formCategorie = $this->createForm(CategorieTypeForm::class, $categorie, [
            'action' => $this->generateUrl('admin.categorie.ajouter'),
            'method' => 'POST',
        ]);
        $formCategorie->handleRequest($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {
            $this->categorieRepository->add($categorie);
            $this->addFlash('success', "Catégorie  \"{$categorie->getName()}\" ajoutée.");
            return $this->redirectToRoute('admin.categories');
        }

        return $this->render(self::CHEMINCATEGORIES, [
                'categories' => $categories,
                'formcategorie' => $formCategorie->createView()
        ]);
    }

    #[Route('/admin/categories/tri/{champ}/{ordre}', name: 'admin.categories.sort')]
    public function sort($champ, $ordre): Response
    {
        $categorie = new Categorie();
        $formCategorie = $this->createForm(CategorieTypeForm::class, $categorie, [
            'action' => $this->generateUrl('admin.categorie.ajouter'),
            'method' => 'POST',
        ]);

        if ($champ === 'name') {
            $categories = $this->categorieRepository->findAllOrderByName($ordre);
        }
        if ($champ === 'nbFormations') {
            $categories = $this->categorieRepository->findAllOrderByFormationsCount($ordre);
        }

        return $this->render(self::CHEMINCATEGORIES, [
                'categories' => $categories,
                'formcategorie' => $formCategorie->createView()
        ]);
    }

    #[Route('/admin/categories/recherche/{champ}/{table}', name: 'admin.categories.findallcontain')]
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $categorie = new Categorie();
        $formCategorie = $this->createForm(CategorieTypeForm::class, $categorie, [
            'action' => $this->generateUrl('admin.categorie.ajouter'),
            'method' => 'POST',
        ]);

        $valeur = $request->get("recherche");
        $categories = $this->categorieRepository->findByContainValue($champ, $valeur, $table);
        return $this->render(self::CHEMINCATEGORIES, [
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table,
                'formcategorie' => $formCategorie->createView()
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
    #[Route('/admin/categories/supprimer/{id<\d+>}', name: 'admin.categorie.supprimer')]
    public function supprimer($id, Request $request): Response
    {
        $categorie = $this->categorieRepository->find($id);
        $formationsCount = $categorie->getFormations()->count();

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }

        if (!$this->isCsrfTokenValid('supprimer_categorie_' . $categorie->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin.categories');
        }

        if ($formationsCount > 0) {
            $this->addFlash('warning', "La categorie \"{$categorie->getName()}\" contient $formationsCount formations et ne peut pas être supprimée.");
            return $this->redirectToRoute('admin.categories');
        }

        $this->categorieRepository->remove($categorie);
        $this->addFlash('success', "Catégorie \"{$categorie->getName()}\" supprimée.");

        return $this->redirectToRoute('admin.categories');
    }

    /**
     * Ajoute une nouvelle catégorie.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/categorie/ajouter', name: 'admin.categorie.ajouter')]
    public function ajouter(Request $request): Response
    {
        $categorie = new Categorie();
        $formCategorie = $this->createForm(CategorieTypeForm::class, $categorie);
        $formCategorie->handleRequest($request);

        if ($formCategorie->isSubmitted() && $formCategorie->isValid()) {

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
