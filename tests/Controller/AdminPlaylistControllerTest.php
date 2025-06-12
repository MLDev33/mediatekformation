<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Categorie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class AdminPlaylistControllerTest
 *
 * Teste les fonctionnalités du controller AdminPlaylistController.
 *
 */
class AdminPlaylistControllerTest extends WebTestCase
{

    /**
     * Crée et persiste un nouvel utilisateur avec le rôle admin.
     *
     * @return User L'utilisateur créé.
     */
    private function newUser(): User
    {
        $container = static::getContainer();

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $entityManager = $container->get('doctrine')->getManager();

        $user = new User();
        $user->setUsername('test_admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, 'adminpass'));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Crée un client authentifié avec un utilisateur administrateur.
     *
     * @return KernelBrowser Le client authentifié.
     */
    private function getAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $user = $this->newUser();

        // Se connecte automatiquement
        $client->loginUser($user);

        return $client;
    }

    /**
     * Vérifie que la page d’index des playlists s'affiche correctement.
     */
    public function testIndex()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/playlists');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    /**
     * Vérifie le tri des playlists par nom croissant.
     */
    public function testSortByNameASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/playlists/tri/name/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    /**
     * Vérifie le tri des playlists par nom décroissant.
     */
    public function testSortByNameDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/playlists/tri/name/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Visual Studio 2019 et C#');
    }

    /**
     * Vérifie le tri des playlists par nombre de formations croissant.
     */
    public function testSortByNbFormationASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/playlists/tri/nbFormations/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Cours de programmation objet');
    }

    /**
     * Vérifie le tri des playlists par nombre de formations décroissant.
     */
    public function testSortByNbFormationDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/playlists/tri/nbFormations/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation (C#)');
    }

    /**
     * Vérifie le filtrage des playlists par nom contenant une chaîne donnée.
     */
    public function testFindAllContainByPlaylistName()
    {
        $client = $this->getAuthenticatedClient();
        $client->request('GET', '/admin/playlists/recherche/name');
        $crawler = $client->submitForm('filtrer', [
            'recherche' => 'java'
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'playlist');
        $this->assertCount(2, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', 'Eclipse et Java');
    }

    /**
     * Vérifie le filtrage des playlists selon une catégorie spécifique.
     */
    public function testFindAllByCategorieId()
    {
        $client = $this->getAuthenticatedClient();

        // Chercher l'ID exact de la catégorie "SQL"
        $container = static::getContainer();
        $categorie = $container->get('doctrine')->getRepository(Categorie::class)->findOneBy(['name' => 'SQL']);
        $categorieId = $categorie->getId();

        $client->request('POST', '/admin/playlists/recherche/id/categories', [
            'recherche' => $categorieId
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertCount(8, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', 'Cours Curseurs');
    }

    /**
     * Vérifie que le bouton "Modifier" d’une playlist redirige vers la page correcte.
     */
    public function testLinkButtonEdit()
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', 'admin/playlists');

        // Recherche le lien "Modifier" correspondant à l’ID
        $modifierLink = $crawler->filter('a[href="/admin/playlist/modifier/27"]');

        // Clique sur ce lien
        $client->click($modifierLink->link());

        $response = $client->getResponse();

        //la page est accessible
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // récupération de la route et contrôle qu'elle est correcte
        $uri = $client->getRequest()->server->get("REQUEST_URI");
        $this->assertEquals('/admin/playlist/modifier/27', $uri);

        // Vérifie qu'un titre de formulaire s'affiche
        $this->assertSelectorExists('h2');

        // La page contient un h4 avec le nom correspondant à la bonne playlist
        $this->assertSelectorTextContains('h2', 'Cours de programmation objet');
    }
}
