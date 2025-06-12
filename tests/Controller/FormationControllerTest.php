<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Tests fonctionnels du FormationController
 *
 * Vérifie :
 * - Affichage de la page /formations
 * - Tri ASC/DESC par différents critères
 * - Recherche par titre, playlist, catégorie
 * - Navigation vers une formation via miniature
 */
class FormationControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    public function testSortByNameASC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/title/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Android Studio (complément n°1) : Navigation Drawer et Fragment');
    }

    public function testSortByNameDESC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/title/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'UML : Diagramme de paquetages');
    }

    public function testSortByPlaylistASC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/name/ASC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation n°74 - POO : collections');
    }

    public function testSortByPlaylistDESC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/name/DESC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
    }

    public function testSortByDateASC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/publishedAt/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Cours UML (8 à 11 / 33) : diagramme de classes');
    }

    public function testSortByDateDESC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations/tri/publishedAt/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    public function testFindAllContainByFormationName()
    {
        $client = static::createClient();
        $client->request('GET', '/formations/recherche/title');
        $crawler = $client->submitForm('filtrer', [
            'recherche' => 'C#'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertCount(11, $crawler->filter('h5'));
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
    }

    public function testFindAllContainByPlaylistName()
    {
        $client = static::createClient();

        $client->request('POST', '/formations/recherche/name/playlist', [
            'recherche' => 'POO'
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertCount(6, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', 'POO TP Java n°6 : polymorphisme');
    }

    public function testFindAllByCategorieId()
    {
        $client = static::createClient();

        // Chercher l'ID exact de la catégorie "POO"
        $container = static::getContainer();
        $categorie = $container->get('doctrine')->getRepository(Categorie::class)->findOneBy(['name' => 'POO']);
        $categorieId = $categorie->getId();

        $client->request('POST', '/formations/recherche/id/categories', [
            'recherche' => $categorieId
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertCount(33, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', "C# : Sérialisation d'objets");
    }

    public function testLinkVideo()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/formations');

        // Récupère le premier lien contenant une image (miniature)
        $link = $crawler->filter('table tbody tr:first-child td a')->link();

        // clic sur n lien (la miniature d'une vidéo)
        $client->click($link);
        // récupération du résultat du clic
        $response = $client->getResponse();
        // contrôle si le lien existe
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        // récupération de la route et contrôle qu'elle est correcte
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        $this->assertEquals('/formations/1', $uri);
        $this->assertSelectorExists('h4');
        $this->assertSelectorTextContains('h4', 'Eclipse n°8 : Déploiement');
    }
}

