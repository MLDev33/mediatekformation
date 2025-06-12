<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Tests fonctionnels du contrôleur AdminFormationController.
 *
 * Couvre la liste, le tri et la recherche des formations dans la partie admin.
 *
 */
class AdminFormationControllerTest extends WebTestCase
{

    /**
     * Crée et persiste un utilisateur avec le rôle administrateur.
     *
     * @return User L'entité User créée avec le rôle ROLE_ADMIN.
     */
    private function newUser(): User
    {
        // Récupération du conteneur de services Symfony
        $container = static::getContainer();

        // Service de hachage de mot de passe
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        // Création d'un nouvel utilisateur admin
        $user = new User();
        $user->setUsername('test_admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($hasher->hashPassword($user, 'adminpass'));

        // Persistance en base de données
        $entityManager = $container->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Crée un client HTTP authentifié avec un utilisateur admin.
     *
     * @return KernelBrowser Client authentifié pour les tests fonctionnels.
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
     * Test de chargement de la page principale des formations en admin.
     *
     * Vérifie la présence des en-têtes et d'une formation connue.
     *
     * @return void
     */
    public function testIndex()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    /**
     * Test le tri par nom de formation dans l'ordre ascendant.
     *
     * @return void
     */
    public function testSortByNameASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/title/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Android Studio (complément n°1) : Navigation Drawer et Fragment');
    }

    /**
     * Test le tri par nom de formation dans l'ordre descendant.
     *
     * @return void
     */
    public function testSortByNameDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/title/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'UML : Diagramme de paquetages');
    }

    /**
     * Test le tri par nom de playlist dans l'ordre ascendant.
     *
     * @return void
     */
    public function testSortByPlaylistASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/name/ASC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Bases de la programmation n°74 - POO : collections');
    }

    /**
     * Test le tri par nom de playlist dans l'ordre descendant.
     *
     * @return void
     */
    public function testSortByPlaylistDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/name/DESC/playlist');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'C# : ListBox en couleur');
    }

    /**
     * Test le tri par nom de date dans l'ordre ascendant.
     *
     * @return void
     */
    public function testSortByDateASC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/publishedAt/ASC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Cours UML (8 à 11 / 33) : diagramme de classes');
    }

    /**
     * Test le tri par nom de date dans l'ordre descendant.
     *
     * @return void
     */
    public function testSortByDateDESC()
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/admin/formations/tri/publishedAt/DESC');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('th', 'formation');
        $this->assertSelectorTextContains('h5', 'Eclipse n°8 : Déploiement');
    }

    /**
     * Test la recherche des formations contenant une chaîne dans leur titre.
     *
     * @return void
     */
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

    /**
     * Test la recherche des formations par ID de catégorie.
     *
     * @return void
     */
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
