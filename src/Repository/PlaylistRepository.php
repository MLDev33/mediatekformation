<?php

namespace App\Repository;

use App\Entity\Playlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Playlist.
 *
 * @extends ServiceEntityRepository<Playlist>
 *
 * @package App\Repository
 */
class PlaylistRepository extends ServiceEntityRepository
{

    /**
     * Constructeur.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    /**
     * Ajoute une playlist en base.
     *
     * @param Playlist $entity
     * @return void
     */
    public function add(Playlist $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Supprime une playlist en base.
     *
     * @param Playlist $entity
     * @return void
     */
    public function remove(Playlist $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne toutes les playlists triées par nom.
     *
     * @param string $ordre Ordre de tri ('ASC' ou 'DESC')
     * @return Playlist[]
     */
    public function findAllOrderByName($ordre): array
    {
        return $this->createQueryBuilder('p')
                ->leftjoin('p.formations', 'f')
                ->groupBy('p.id')
                ->orderBy('p.name', $ordre)
                ->getQuery()
                ->getResult();
    }

    /**
     * Recherche des playlists dont un champ contient une valeur donnée,
     * ou retourne toutes les playlists si la valeur est vide.
     *
     * @param string $champ Champ à filtrer
     * @param string $valeur Valeur à chercher
     * @param string $table Nom de la relation (optionnel)
     * @return Playlist[]
     */
    public function findByContainValue($champ, $valeur, $table = ""): array
    {
        if ($valeur == "") {
            return $this->findAllOrderByName('ASC');
        }
        if ($table == "") {
            return $this->createQueryBuilder('p')
                    ->leftjoin('p.formations', 'f')
                    ->where('p.' . $champ . ' LIKE :valeur')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->groupBy('p.id')
                    ->orderBy('p.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        } else {
            return $this->createQueryBuilder('p')
                    ->leftjoin('p.formations', 'f')
                    ->leftjoin('f.categories', 'c')
                    ->where('c.' . $champ . ' LIKE :valeur')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->groupBy('p.id')
                    ->orderBy('p.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        }
    }

    /**
     * Retourne toutes les playlists triées par le nombre de formations associées.
     *
     * @param string $ordre Ordre de tri ('ASC' ou 'DESC')
     * @return Playlist[]
     */
    public function findAllOrderByFormationsCount($ordre): array
    {
        return $this->createQueryBuilder('p')
                ->leftjoin('p.formations', 'f')
                ->groupBy('p.id')
                ->orderBy('COUNT(f.id)', $ordre)
                ->getQuery()
                ->getResult();
    }
}
