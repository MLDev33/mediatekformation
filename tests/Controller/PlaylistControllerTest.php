<?php

namespace App\Tests\Controller;

use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of newPHPClass
 *
 * @author m-lordiportable
 */
class PlaylistControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    public function testSortByNameASC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists/tri/name/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    public function testSortByNameDESC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists/tri/name/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Visual Studio 2019 et C#');
    }

    public function testSortByNbFormationASC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists/tri/nbFormations/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Cours de programmation objet');
    }

    public function testSortByNbFormationDESC()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/playlists/tri/nbFormations/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    public function testFindAllContainByPlaylistName()
    {
        $client = static::createClient();
        $client->request('GET', '/playlists/recherche/name');
        $crawler = $client->submitForm('filtrer', [
            'recherche' => 'java'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertCount(2, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', 'Eclipse et Java');
    }

    public function testFindAllContainByCategorieId()
    {
        $client = static::createClient();

        // Chercher l'ID exact de la catégorie "SQL"
        $container = static::getContainer();
        $categorie = $container->get('doctrine')->getRepository(Categorie::class)->findOneBy(['name' => 'SQL']);
        $categorieId = $categorie->getId();

        $client->request('POST', 'playlists/recherche/id/categories', [
            'recherche' => $categorieId
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertCount(8, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', 'Cours Curseurs');
    }

            public function testLinkButtonDetail()
    {
        $client = static::createClient();
        $client->request('GET', '/playlists');
        $client->clickLink('Voir détail');
        $response = $client->getResponse();
        //la page est accessible
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
         // récupération de la route et contrôle qu'elle est correcte
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        $this->assertEquals('/playlists/playlist/13', $uri);
        // La page contient un h4 avec le nom correspondant à la bonne playlist
        $this->assertSelectorTextContains('h4', 'Bases de la programmation (C#)');

    }
}
