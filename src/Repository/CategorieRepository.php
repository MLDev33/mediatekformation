<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    public function add(Categorie $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Categorie $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne la liste des catégories des formations d'une playlist
     * @param type $idPlaylist
     * @return array
     */
    public function findAllForOnePlaylist($idPlaylist): array
    {
        return $this->createQueryBuilder('c')
                ->join('c.formations', 'f')
                ->join('f.playlist', 'p')
                ->where('p.id=:id')
                ->setParameter('id', $idPlaylist)
                ->orderBy('c.name', 'ASC')
                ->getQuery()
                ->getResult();
    }

    /**
     * Retourne toutes les catégories triées sur le nom
     * @param type $champ
     * @param type $ordre
     * @return Playlist[]
     */
    public function findAllOrderByName($ordre): array
    {
        return $this->createQueryBuilder('c')
                ->leftjoin('c.formations', 'f')
                ->groupBy('c.id')
                ->orderBy('c.name', $ordre)
                ->getQuery()
                ->getResult();
    }

    public function findByContainValue($champ, $valeur, $table = ""): array
    {
        if ($valeur == "") {
            return $this->findAllOrderByName('ASC');
        }
        if ($table == "") {
            return $this->createQueryBuilder('c')
                    ->leftjoin('c.formations', 'f')
                    ->where('c.' . $champ . ' LIKE :valeur')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->groupBy('c.id')
                    ->orderBy('c.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        } else {
            return $this->createQueryBuilder('c')
                    ->leftjoin('c.formations', 'f')
                    ->leftjoin('f.categories', 'c')
                    ->where('c.' . $champ . ' LIKE :valeur')
                    ->setParameter('valeur', '%' . $valeur . '%')
                    ->groupBy('c.id')
                    ->orderBy('c.name', 'ASC')
                    ->getQuery()
                    ->getResult();
        }
    }

    public function findAllOrderByFormationsCount($ordre): array
    {
        return $this->createQueryBuilder('c')
                ->leftjoin('c.formations', 'f')
                ->groupBy('c.id')
                ->orderBy('COUNT(c.id)', $ordre)
                ->getQuery()
                ->getResult();
    }

    public function findOneByName(string $name): ?Categorie
    {
        return $this->createQueryBuilder('c')
                ->where('LOWER(c.name) = :name')
                ->setParameter('name', strtolower($name))
                ->getQuery()
                ->getOneOrNullResult();
    }
}
