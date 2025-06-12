<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test fonctionnel du contrôleur d'accueil.
 *
 * Vérifie que la page d'accueil est accessible et retourne un code HTTP 200.
 *
 */
class AccueilControllerTest extends WebTestCase
{

    /**
     * Teste l'accès à la page d'accueil.
     *
     * Envoie une requête GET vers la route '/' et vérifie que la réponse
     * a un code HTTP 200 (OK).
     */
    public function testAccesPage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
