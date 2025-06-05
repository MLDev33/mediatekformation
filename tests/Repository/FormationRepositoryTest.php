<?php

namespace App\Tests\Repository;

use DateTime;
use App\Entity\Formation;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of FormationRepositoryTest
 *
 * @author m-lordiportable
 */
class FormationRepositoryTest extends KernelTestCase
{

    /**
     * Créer un objet Formation (valide)
     */
    public function newFormation(): Formation
    {
        $formation = (new Formation())
            ->setTitle('Formation Symfony')
            ->setDescription('Description test')
            ->setPublishedAt(new DateTime('2025-05-31'))
            ->setVideoId('abc123XYZ');

        return $formation;
    }

    /**
     *
     */
    public function recupRepository(): FormationRepository
    {
        self::bootKernel();
        $repository = self::getContainer()->get(FormationRepository::class);
        return $repository;
    }

    public function testNbFormations()
    {
        $formationRepository = $this->recupRepository();
        $nbFormations = $formationRepository->count([]);
        $this->assertEquals(236, $nbFormations);
    }

    public function testAddFormation()
    {
        $formationRepository = $this->recupRepository();
        $formation = $this->newFormation();
        $nbFormations = $formationRepository->count([]);
        $formationRepository->add($formation, true);
        $this->assertEquals($nbFormations + 1, $formationRepository->count([]), "erreur lors de l'ajout");
    }

    public function testRemoveFormation()
    {
        $formationRepository = $this->recupRepository();
        $formation = $this->newFormation();
        $formationRepository->add($formation, true);
        $nbFormations = $formationRepository->count([]);
        $formationRepository->remove($formation, true);
        $this->assertEquals($nbFormations - 1, $formationRepository->count([]), "erreur lors de la suppression");
    }

    public function testFindAllOrderBy()
    {
        $formationRepository = $this->recupRepository();
        $formationA = $this->newFormation()->setTitle('A');
        $formationZ = $this->newFormation()->setTitle('ZZZ');
        $formationRepository->add($formationA, true);
        $formationRepository->add($formationZ, true);

        $formationsASC = $formationRepository->findAllOrderBy('title', 'ASC');
        $this->assertEquals('A', $formationsASC[0]->getTitle());

        $formationsDESC = $formationRepository->findAllOrderBy('title', 'DESC');
        $this->assertEquals('ZZZ', $formationsDESC[0]->getTitle());
    }

    public function testFindByContainValue()
    {
        $formationRepository = $this->recupRepository();
        $formation = $this->newFormation();
        $formationRepository->add($formation, true);
        $formations = $formationRepository->findByContainValue('title', 'Formation Symfony');
        $nbFormations = count($formations);
        $this->assertEquals(1, $nbFormations);
        $this->assertEquals('Formation Symfony', $formations[0]->getTitle());
    }

    public function testFindAllLasted()
    {
        $formationRepository = $this->recupRepository();
        $formationLast1 = $this->newFormation()->setTitle('Ancienne')->setPublishedAt(new DateTime('-15 day'));
        $formationLast2 = $this->newFormation()->setTitle('AvantDerniere')->setPublishedAt(new DateTime('-1 day'));
        $formationLast3 = $this->newFormation()->setTitle('Derniere')->setPublishedAt(new DateTime('today'));

        $formationRepository->add($formationLast1, true);
        $formationRepository->add($formationLast2, true);
        $formationRepository->add($formationLast3, true);

        $formations = $formationRepository->findAllLasted(2);

        $this->assertCount(2, $formations, "Devrait retourner 2 formations");

        $this->assertEquals('Derniere', $formations[0]->getTitle(), "La plus récente doit être en premier");
        $this->assertEquals('AvantDerniere', $formations[1]->getTitle(), "La deuxième plus récente doit suivre");
    }

    public function testFindAllForOnePlaylist()
    {
        $formationRepository = $this->recupRepository();
        $formation = $this->newFormation();
        $formationRepository->add($formation, true);
        $formations = $formationRepository->findAllForOnePlaylist(2);
        $this->assertCount(11, $formations, "Devrait retourner 11 formations");
    }
}
