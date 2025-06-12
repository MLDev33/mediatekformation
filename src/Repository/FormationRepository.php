<?php

namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Formation.
 *
 * @extends ServiceEntityRepository<Formation>
 *
 * @package App\Repository
 */
class FormationRepository extends ServiceEntityRepository
{

    /**
     * Constructeur.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formation::class);
    }

    /**
     * Ajoute une formation en base de données.
     *
     * @param Formation $entity
     * @return void
     */
    public function add(Formation $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Supprime une formation de la base de données.
     *
     * @param Formation $entity
     * @return void
     */
    public function remove(Formation $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne toutes les formations triées sur un champ donné.
     *
     * @param string $champ Champ de tri.
     * @param string $ordre Ordre de tri ('ASC' ou 'DESC').
     * @param string $table Nom de la relation si le champ appartient à une autre table (optionnel).
     * @return Formation[]
     */
    public function findAllOrderBy($champ, $ordre, $table = ""): array
    {
        if ($table == "") {
            return $this->createQueryBuilder('f')
                    ->orderBy('f.' . $champ, $ordre)
                    ->getQuery()
                    ->getResult();
        } else {
            return $this->createQueryBuilder('f')
                    ->join('f.' . $table, 't')
                    ->orderBy('t.' . $champ, $ordre)
                    ->getQuery()
                    ->getResult();
        }
    }

    /**
     * Recherche des formations dont un champ contient une valeur donnée.
     * Si la valeur est vide, retourne toutes les formations.
     *
     * @param string $champ Champ à rechercher.
     * @param string $valeur Valeur à chercher.
     * @param string $table Relation pour le champ (optionnel).
     * @return Formation[]
     */
    public function findByContainValue($champ, $valeur, $table = ""): array
    {
        if ($valeur == "") {
            return $this->findAll();
        }
        if ($table == "") {
            return $this->createQueryBuilder('f')
                    ->where('f.' . $champ . ' LIKE :valeur')
                    ->orderBy('f.publishedAt', 'DESC')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->getQuery()
                    ->getResult();
        } else {
            return $this->createQueryBuilder('f')
                    ->join('f.' . $table, 't')
                    ->where('t.' . $champ . ' LIKE :valeur')
                    ->orderBy('f.publishedAt', 'DESC')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->getQuery()
                    ->getResult();
        }
    }

    /**
     * Retourne les n formations les plus récentes.
     *
     * @param int $nb Nombre de formations à retourner.
     * @return Formation[]
     */
    public function findAllLasted($nb): array
    {
        return $this->createQueryBuilder('f')
                ->orderBy('f.publishedAt', 'DESC')
                ->setMaxResults($nb)
                ->getQuery()
                ->getResult();
    }

    /**
     * Retourne toutes les formations d'une playlist donnée.
     *
     * @param int $idPlaylist Identifiant de la playlist.
     * @return Formation[]
     */
    public function findAllForOnePlaylist($idPlaylist): array
    {
        return $this->createQueryBuilder('f')
                ->join('f.playlist', 'p')
                ->where('p.id=:id')
                ->setParameter('id', $idPlaylist)
                ->orderBy('f.publishedAt', 'ASC')
                ->getQuery()
                ->getResult();
    }
}
