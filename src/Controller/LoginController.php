<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant l'authentification des utilisateurs.
 *
 * Fournit les actions de connexion et déconnexion.
 */
final class LoginController extends AbstractController
{

    /**
     * Affiche le formulaire de connexion.
     *
     * Récupère l'erreur d'authentification éventuelle et le dernier nom d'utilisateur saisi,
     * puis affiche la page de login avec ces informations.
     *
     * @param AuthenticationUtils $authenticationUtils Service fournissant les informations d'authentification
     * @return Response Réponse HTTP avec le rendu de la page de connexion
     */
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupération de la dernière erreur d'authentification, s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupération du dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Rendu du template de connexion avec les données nécessaires
        return $this->render('login/index.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error
        ]);
    }

    /**
     * Action de déconnexion.
     *
     * Cette méthode doit rester vide : Symfony gère la déconnexion via la configuration de sécurité.
     *
     * @throws \LogicException Exception levée si cette méthode est appelée directement, car elle doit être interceptée par le pare-feu de sécurité Symfony.
     */
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        throw new \LogicException('Cette méthode doit être interceptée par le pare-feu de sécurité de Symfony.');
    }
}
