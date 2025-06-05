<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Description of AdminCategorieControllerTest
 *
 * @author m-lordiportable
 */
class AdminCategorieControllerTest extends WebTestCase
{

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

    private function getAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $user = $this->newUser();

        // Se connecte automatiquement
        $client->loginUser($user);

        return $client;
    }

    public function testIndex()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'Android');
    }

    public function testSortByNameASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/name/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'Android');
    }

    public function testSortByNameDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/name/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'UML');
    }

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

    public function testSortByNbFormationASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/nbFormations/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'UML');
    }

    public function testSortByNbFormationDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/categories/tri/nbFormations/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'catégorie');
        $this->assertSelectorTextContains('h5', 'C#');
    }
}
