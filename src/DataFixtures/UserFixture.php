<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixture pour créer un utilisateur administrateur initial dans la base.
 *
 * Cette fixture insère un utilisateur "admin" avec un mot de passe hashé
 * et le rôle ROLE_ADMIN. Utile pour initialiser une installation avec un compte
 * admin fonctionnel.
 */
class UserFixture extends Fixture
{

    /**
     * Service pour hasher les mots de passe utilisateurs.
     *
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * Injection du service UserPasswordHasherInterface via le constructeur.
     *
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Charge les données fixtures dans la base.
     *
     * Crée un utilisateur admin avec un mot de passe "admin" hashé,
     * puis persiste et flush l'entité.
     *
     * @param ObjectManager $manager Gestionnaire d'entités Doctrine
     */
    public function load(ObjectManager $manager): void
    {
        // Création d'un nouvel utilisateur
        $user = new User();

        // Définition du nom d'utilisateur
        $user->setUsername("admin");

        // Mot de passe en clair à hasher
        $plaintextPassword = "admin";

        // Hashage du mot de passe sécurisé
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        // Assignation du mot de passe hashé
        $user->setPassword($hashedPassword);

        // Attribution du rôle administrateur
        $user->setRoles(['ROLE_ADMIN']);

        // Persistance de l'entité en attente d'écriture en base
        $manager->persist($user);

        // Écriture en base de données
        $manager->flush();
    }
}
