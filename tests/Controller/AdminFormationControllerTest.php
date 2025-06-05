<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Description of newPHPClass
 *
 * @author m-lordiportable
 */
class AdminFormationControllerTest extends WebTestCase
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
        $crawler = $client->request('GET', '/admin/formations');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    public function testSortByNameASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/title/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Android Studio (complément n°1) : Navigation Drawer et Fragment');
    }

    public function testSortByNameDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/title/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'UML : Diagramme de paquetages');
    }

    public function testSortByPlaylistASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/name/ASC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation n°74 - POO : collections');
    }

    public function testSortByPlaylistDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/name/DESC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
    }

    public function testSortByDateASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/publishedAt/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Cours UML (8 à 11 / 33) : diagramme de classes');
    }

    public function testSortByDateDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/publishedAt/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    public function testFindAllContainByFormationName()
    {
        $client = $this->getAuthenticatedClient();

        $client->request('POST', '/admin/formations/recherche/title', [
            'recherche' => 'C#'
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertCount(11, $crawler->filter('h5'));
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
    }

    public function testFindAllByCategorieId()
    {
        $client = $this->getAuthenticatedClient();

        // Chercher l'ID exact de la catégorie "POO"
        $container = static::getContainer();
        $categorie = $container->get('doctrine')->getRepository(Categorie::class)->findOneBy(['name' => 'POO']);
        $categorieId = $categorie->getId();

        $client->request('POST', '/admin/formations/recherche/id/categories', [
            'recherche' => $categorieId
        ]);

        $this->assertResponseIsSuccessful();
        $crawler = $client->getCrawler();
        $this->assertCount(33, $crawler->filter('tbody tr'));
        $this->assertSelectorTextContains('h5', "C# : Sérialisation d'objets");
    }
}
