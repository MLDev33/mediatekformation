<?php

namespace App\Tests\Validations;

use App\Entity\Formation;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Description of formationValidationsTest
 *
 * @author m-lordiportable
 */
class FormationValidationsTest extends KernelTestCase
{

    /**
     * Créer un objet Formation (valide)
     */
    public function getFormation(): Formation
    {
        $formation = (new Formation())
                ->setTitle('Formation Symfony')
                ->setDescription('Description test')
                ->setPublishedAt(new DateTime('2025-05-31'))
                ->setVideoId('abc123XYZ');
        return $formation;
    }

    /**
     * Méthode d’assertion personnalisée pour la validation
     */
    public function assertHasErrors(Formation $formation, int $expectedErrorCount): void
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $error = $validator->validate($formation); // ici, c'était `$error` par erreur
        $this->assertCount($expectedErrorCount, $error);
    }

    /**
     * Test : date dans le passé → OK
     */
    public function testPublishedAtInPast(): void
    {
        $formation = $this->getFormation()->setPublishedAt(new DateTime('-1 day'));
        $this->assertHasErrors($formation, 0);
    }

    /**
     * Test : date aujourd'hui → OK
     */
    public function testPublishedAtToday(): void
    {
        $formation = $this->getFormation()->setPublishedAt(new DateTime('today')); // aujourd'hui

        $this->assertHasErrors($formation, 0);
    }

    /**
     * Test : date future → ERREUR
     */
    public function testPublishedAtInFuture(): void
    {
        $formation = $this->getFormation()->setPublishedAt(new DateTime('tomorrow 00:00:01'));
        $this->assertHasErrors($formation, 1);
    }
}
