<?php

namespace App\Tests\Repository;

use App\Entity\Playlist;
use App\Repository\PlaylistRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Description of PlaylistRepositoryTest
 *
 * @author m-lordiportable
 */
class PlaylistRepositoryTest extends KernelTestCase
{

    public function newPlaylist(): Playlist
    {
        $playlist = (new Playlist())
            ->setName('playlist test Symfony')
            ->setDescription('Description test playlistRepository');

        return $playlist;
    }

    public function recupRepository()
    {
        self::bootKernel();
        $repository = self::getContainer()->get(PlaylistRepository::class);
        return $repository;
    }

    public function testAddPlaylist()
    {
        $playlistRepository = $this->recupRepository();
        $playlist = $this->newPlaylist();
        $nbFormations = $playlistRepository->count([]);
        $playlistRepository->add($playlist, true);
        $this->assertEquals($nbFormations + 1, $playlistRepository->count([]), "erreur lors de l'ajout");
    }

    public function testRemovePlaylist()
    {
        $playlistRepository = $this->recupRepository();
        $playlist = $this->newPlaylist();
        $playlistRepository->add($playlist, true);
        $nbFormations = $playlistRepository->count([]);
        $playlistRepository->remove($playlist, true);
        $this->assertEquals($nbFormations - 1, $playlistRepository->count([]), "erreur lors de la suppression");
    }

    public function testFindAllOrderByName()
    {
        $playlistRepository = $this->recupRepository();
        $playlistA = $this->newPlaylist()->setName('A');
        $playlistZ = $this->newPlaylist()->setName('ZZZ');
        $playlistRepository->add($playlistA, true);
        $playlistRepository->add($playlistZ, true);

        $playlistsASC = $playlistRepository->findAllOrderByName('ASC');
        $this->assertEquals('A', $playlistsASC[0]->getName());

        $playlistsDESC = $playlistRepository->findAllOrderByName('DESC');
        $this->assertEquals('ZZZ', $playlistsDESC[0]->getName());
    }

    public function testFindByContainValue()
    {
        $playlistRepository = $this->recupRepository();
        $playlist = $this->newPlaylist();
        $playlistRepository->add($playlist, true);
        $playlists = $playlistRepository->findByContainValue('name', 'playlist test Symfony');
        $nbFormations = count($playlists);
        $this->assertEquals(1, $nbFormations);
        $this->assertEquals('playlist test Symfony', $playlists[0]->getName());
    }

    public function testFindAllOrderByFormationsCount()
    {
        $playlistRepository = $this->recupRepository();

        $playlistVide = $this->newPlaylist()->setName('Playlist vide');

        $playlistRepository->add($playlistVide, true);

        $playlistsASC = $playlistRepository->findAllOrderByFormationsCount('ASC');
        $this->assertEquals('Playlist vide', $playlistsASC[0]->getName());

        $playlistsDESC = $playlistRepository->findAllOrderByFormationsCount('DESC');
        $this->assertEquals('Bases de la programmation (C#)', $playlistsDESC[0]->getName());
    }
}
