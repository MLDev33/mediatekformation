<?php

namespace App\tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Formation;

/**
 * Description of FormationTest
 *
 * @author m-lordiportable
 */
class FormationTest extends TestCase
{

    public function testDatecreationString()
    {
        $formation = new Formation();
        $formation->setPublishedAt(new \DateTime("2025-05-31"));
        $this->assertEquals("31/05/2025", $formation->getPublishedAtString());
    }

    public function testGetPublishedAtStringWhenNull()
    {
        $formation = new Formation();
        $formation->setPublishedAt(null);

        $this->assertEquals('', $formation->getPublishedAtString());
    }
}
