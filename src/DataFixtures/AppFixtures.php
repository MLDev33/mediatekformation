<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Classe de fixtures principale pour insérer des données initiales en base.
 *
 * Cette classe sert à charger des données de test ou des données de référence
 * dans la base via Doctrine Fixtures Bundle.
 *
 */
class AppFixtures extends Fixture
{

    /**
     * Charge les données fixtures dans la base.
     *
     * @param ObjectManager $manager Gestionnaire d'entités Doctrine, utilisé pour persister les entités
     */
    public function load(ObjectManager $manager): void
    {
        // Exemple : création d'une entité Product fictive
        // $product = new Product();
        // $manager->persist($product);
        // Exécution de l'insertion en base
        $manager->flush();
    }
}
