<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 *  Contrôleur qui gère les pages d'accueil et des CGU.
 *
 * Utilise le FormationRepository pour récupérer les données nécessaires.
 */
class AccueilController extends AbstractController
{

    /**
     * Repository pour accéder aux formations.
     *
     * @var FormationRepository
     */
    private FormationRepository $repository;

    /**
     * Initialise le contrôleur avec le repository de formations.
     *
     * @param FormationRepository $repository   Repository des formations
     */
    public function __construct(FormationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Affiche la page d'accueil avec les dernières formations.
     *
     * Récupère les 2 dernières formations et les passe à la vue Twig pour affichage.
     *
     * @return Response La réponse HTTP contenant la page d'accueil.
     */
    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        $formations = $this->repository->findAllLasted(2);
        return $this->render("pages/accueil.html.twig", [
                'formations' => $formations
        ]);
    }

    /**
     * Affiche la page des conditions générales d'utilisation.
     *
     * @return Response
     */
    #[Route('/cgu', name: 'cgu')]
    public function cgu(): Response
    {
        return $this->render("pages/cgu.html.twig");
    }
}
