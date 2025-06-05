<?php

namespace App\Tests\Repository;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of CategorieRepositoryTest
 *
 * @author m-lordiportable
 */
class CategorieRepositoryTest extends KernelTestCase
{

    public function newCategorie(): Categorie
    {
        $categorie = (new Categorie())
            ->setName("Atelier de professionnalisation");
        return $categorie;
    }

    public function recupRepository()
    {
        self::bootKernel();
        $repository = self::getContainer()->get(CategorieRepository::class);
        return $repository;
    }

    public function testAddCategorie()
    {
        $categorieRepository = $this->recupRepository();
        $categorie = $this->newCategorie();
        $nbCategories = $categorieRepository->count([]);
        $categorieRepository->add($categorie, true);
        $this->assertEquals($nbCategories + 1, $categorieRepository->count([]), "erreur lors de l'ajout");
    }

    public function testFindAllForOnePlaylist()
    {
        $categorieRepository = $this->recupRepository();
        $categorie = $this->newCategorie();
        $categorieRepository->add($categorie, true);

        $categories = $categorieRepository->findAllForOnePlaylist(12);
        $nbCategories = count($categories);

        $this->assertEquals(2, $nbCategories);
    }

    public function testRemoveCategorie()
    {
        $categorieRepository = $this->recupRepository();
        $categorie = $this->newCategorie();
        $categorieRepository->add($categorie, true);
        $nbCategories = $categorieRepository->count([]);
        $categorieRepository->remove($categorie, true);
        $this->assertEquals($nbCategories - 1, $categorieRepository->count([]), "erreur lors de la suppression");
    }

    public function testFindAllOrderByName()
    {
        $categorieRepository = $this->recupRepository();
        $categorieA = $this->newCategorie()->setName('A');
        $categorieZ = $this->newCategorie()->setName('ZZZ');
        $categorieRepository->add($categorieA, true);
        $categorieRepository->add($categorieZ, true);

        $categoriesASC = $categorieRepository->findAllOrderByName('ASC');
        $this->assertEquals('A', $categoriesASC[0]->getName());

        $categoriesDESC = $categorieRepository->findAllOrderByName('DESC');
        $this->assertEquals('ZZZ', $categoriesDESC[0]->getName());
    }

    public function testFindByContainValue()
    {
        $categorieRepository = $this->recupRepository();
        $categorie = $this->newCategorie();
        $categorieRepository->add($categorie, true);
        $categories = $categorieRepository->findByContainValue('name', 'Atelier de professionnalisation');
        $nbCategories = count($categories);
        $this->assertEquals(1, $nbCategories);
        $this->assertEquals('Atelier de professionnalisation', $categories[0]->getName());
    }

    public function testFindAllOrderByFormationsCount()
    {
        $categorieRepository = $this->recupRepository();

        $categorieVide = $this->newCategorie()->setName('Categorie vide');

        $categorieRepository->add($categorieVide, true);

        $categoriesASC = $categorieRepository->findAllOrderByFormationsCount('ASC');
        $this->assertEquals('Categorie vide', $categoriesASC[0]->getName());

        $categoriesDESC = $categorieRepository->findAllOrderByFormationsCount('DESC');
        $this->assertEquals('C#', $categoriesDESC[0]->getName());
    }

    public function testFindOneByName()
    {
        $categorieRepository = $this->recupRepository();
        $categorie = $this->newCategorie();
        $categorieRepository->add($categorie, true);

        $categories = $categorieRepository->findOneByName('Atelier de professionnalisation');

        $this->assertEquals('Atelier de professionnalisation', $categories->getName());
    }
}
