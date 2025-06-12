<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests fonctionnels pour le contrôleur AdminCategorieController.
 *
 * Vérifie l'accès et le bon fonctionnement des pages d'administration des catégories
 * avec tri, recherche et authentification.
 *
 */
class AdminCategorieControllerTest extends WebTestCase
{

    /**
     * Crée et persiste un utilisateur administrateur en base de test.
     *
     * @return User L'utilisateur admin fraîchement créé
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
     * Crée un client HTTP Symfony authentifié avec un utilisateur admin.
     *
     * @return KernelBrowser Le client connecté
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
     * Test de l'accès à la page principale des catégories admin.
     */
    public function testIndex()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'Android');
    }

    /**
     * Test du tri par nom ascendant.
     */
    public function testSortByNameASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/name/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'Android');
    }

    /**
     * Test du tri par nom descendant.
     */
    public function testSortByNameDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/name/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'UML');
    }

    /**
     * Test de la recherche par nom contenant "Python".
     */
    public function testFindAllContainByName()
    {
        $client = $this->getAuthenticatedClient();

        $client->request('POST', '/admin/categories/recherche/name', [
            'recherche' => 'Python'
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertCount(1, $crawler->filter('h5'));
        $this->assertSelectorTextContains('h5', 'Python');
    }

    /**
     * Test du tri par nombre de formations ascendant.
     */
    public function testSortByNbFormationASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/nbFormations/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'UML');
    }

    /**
     * Test du tri par nombre de formations descendant.
     */
    public function testSortByNbFormationDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/nbFormations/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'C#');
    }
}
