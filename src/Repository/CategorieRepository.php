<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Repository pour l'entité Categorie.
 *
 * Fournit des méthodes personnalisées pour récupérer les catégories.
 *
 * @extends ServiceEntityRepository<Categorie>
 *
 * @package App\Repository
 */
class CategorieRepository extends ServiceEntityRepository
{

    /**
     * Constructeur.
     *
     * @param ManagerRegistry $registry Gestionnaire des entités Doctrine.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Ajoute une catégorie en base de données.
     *
     * @param Categorie $entity Entité à ajouter.
     * @return void
     */
    public function add(Categorie $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Supprime une catégorie de la base de données.
     *
     * @param Categorie $entity Entité à supprimer.
     * @return void
     */
    public function remove(Categorie $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    /**
     * Retourne les catégories liées aux formations d'une playlist donnée.
     *
     * @param int $idPlaylist Identifiant de la playlist.
     * @return Categorie[] Liste des catégories triées par nom.
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
     * Retourne toutes les catégories triées par nom.
     *
     * @param string $ordre Ordre de tri ('ASC' ou 'DESC').
     * @return Categorie[]
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

    /**
     * Recherche des catégories contenant une valeur dans un champ donné.
     *
     * @param string $champ Nom du champ à filtrer.
     * @param string $valeur Valeur à rechercher.
     * @param string $table Nom optionnel d'une table pour jointure supplémentaire.
     * @return Categorie[]
     */
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

    /**
     * Retourne toutes les catégories triées selon le nombre de formations associées.
     *
     * @param string $ordre Ordre de tri ('ASC' ou 'DESC').
     * @return Categorie[]
     */
    public function findAllOrderByFormationsCount($ordre): array
    {
        return $this->createQueryBuilder('c')
                ->leftjoin('c.formations', 'f')
                ->groupBy('c.id')
                ->orderBy('COUNT(c.id)', $ordre)
                ->getQuery()
                ->getResult();
    }

    /**
     * Recherche une catégorie par son nom exact (insensible à la casse).
     *
     * @param string $name Nom de la catégorie.
     * @return Categorie|null
     */
    public function findOneByName(string $name): ?Categorie
    {
        return $this->createQueryBuilder('c')
                ->where('LOWER(c.name) = :name')
                ->setParameter('name', strtolower($name))
                ->getQuery()
                ->getOneOrNullResult();
    }
}
