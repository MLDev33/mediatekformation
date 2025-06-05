<?php

namespace App\Controller\admin;

use App\Entity\Formation;
use App\Form\FormationTypeForm;
use App\Repository\CategorieRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur d'administration des formations.
 */
class AdminFormationsController extends AbstractController
{

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
     * Chemin du template pour la liste des formations
     */
    private const CHEMINFORMATIONS = "admin/formations/admin.formations.html.twig";

    /**
     * Chemin du template pour l'affichage d'une seule formation
     */
    private const CHEMINFORMATION = "admin/formations/admin.formation.html.twig";

    /**
     * Constructeur du contrôleur.
     *
     * @param FormationRepository $formationRepository
     * @param CategorieRepository $categorieRepository
     */
    public function __construct(FormationRepository $formationRepository, CategorieRepository $categorieRepository)
    {
        $this->formationRepository = $formationRepository;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * Affiche la liste des formations.
     *
     * @return Response
     */
    #[Route('/admin/formations', name: 'admin.formations')]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONS, [
                'formations' => $formations,
                'categories' => $categories
        ]);
    }

    /**
     * Trie les formations selon un champ et un ordre donné.
     *
     * @param type $champ
     * @param type $ordre
     * @param type $table
     * @return Response
     */
    #[Route('/admin/formations/tri/{champ}/{ordre}/{table}', name: 'admin.formations.sort')]
    public function sort($champ, $ordre, $table = ""): Response
    {
        $formations = $this->formationRepository->findAllOrderBy($champ, $ordre, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONS, [
                'formations' => $formations,
                'categories' => $categories
        ]);
    }

    /**
     * Recherche des formations contenant une valeur dans un champ donné.
     *
     * @param type $champ
     * @param Request $request
     * @param type $table
     * @return Response
     */
    #[Route('/admin/formations/recherche/{champ}/{table}', name: 'admin.formations.findallcontain')]
    public function findAllContain($champ, Request $request, $table = ""): Response
    {
        $valeur = $request->get("recherche");
        $formations = $this->formationRepository->findByContainValue($champ, $valeur, $table);
        $categories = $this->categorieRepository->findAll();
        return $this->render(self::CHEMINFORMATIONS, [
                'formations' => $formations,
                'categories' => $categories,
                'valeur' => $valeur,
                'table' => $table
        ]);
    }

    /**
     * Affiche une seule formation.
     *
     * @param type $id
     * @return Response
     */
    #[Route('/admin/formation/{id<\d+>}', name: 'admin.formations.showone')]
    public function showOne($id): Response
    {
        $formation = $this->formationRepository->find($id);

        return $this->render(self::CHEMINFORMATION, [ // Renvoie vers la page qui affiche une formation
                'formation' => $formation,
        ]);
    }

    /**
     * Supprime une formation.
     *
     * @param type $id
     * @return Response
     */
    #[Route('/admin/formation/supprimer/{id<\d+>}', name: 'admin.formation.supprimer')]
    public function supprimer($id): Response
    {
        $formation = $this->formationRepository->find($id);
        $this->formationRepository->remove($formation);
        return $this->redirectToRoute('admin.formations');
    }

    /**
     * Modifie une formation existante.
     *
     * @param type $id
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/formation/modifier/{id<\d+>}', name: 'admin.formation.modifier')]
    public function modifier($id, Request $request): Response
    {
        $formation = $this->formationRepository->find($id);
        $formFormation = $this->createForm(FormationTypeForm::class, $formation);

        $formFormation->handleRequest($request);
        if ($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->formationRepository->add($formation);
            return $this->redirectToRoute('admin.formations');
        }

        return $this->render("admin/formations/admin.formation.edit.html.twig", [
                'formation' => $formation,
                'formformation' => $formFormation->createView()
        ]);
    }

    /**
     * Ajoute une nouvelle formation.
     *
     * @param Request $request
     * @return Response
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

        return $this->render("admin/formations/admin.formation.add.html.twig", [
                'formation' => $formation,
                'formformation' => $formFormation->createView()
        ]);
    }
}