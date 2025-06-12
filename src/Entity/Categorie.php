<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant une catégorie de formations.
 *
 * Une catégorie peut être associée à plusieurs formations via une relation ManyToMany.
 *
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{

    /**
     * Identifiant unique auto-incrémenté de la catégorie.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom de la catégorie.
     *
     * @var string|null
     */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    /**
     * Collection des formations associées à cette catégorie.
     *
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, mappedBy: 'categories')]
    private Collection $formations;

    /**
     * Constructeur.
     *
     * Initialise la collection des formations.
     */
    public function __construct()
    {
        $this->formations = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique de la catégorie.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le nom de la catégorie.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la catégorie.
     *
     * @param string|null $name Nom à attribuer
     * @return $this
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Récupère la collection des formations liées à cette catégorie.
     *
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    /**
     * Ajoute une formation à la catégorie.
     *
     * Met à jour également la relation inverse côté Formation.
     *
     * @param Formation $formation Formation à ajouter
     * @return $this
     */
    public function addFormation(Formation $formation): static
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->addCategory($this);
        }

        return $this;
    }

    /**
     * Retire une formation de la catégorie.
     *
     * Met à jour également la relation inverse côté Formation.
     *
     * @param Formation $formation Formation à retirer
     * @return $this
     */
    public function removeFormation(Formation $formation): static
    {
        if ($this->formations->removeElement($formation)) {
            $formation->removeCategory($this);
        }

        return $this;
    }

    /**
     * Retourne le nombre de formations associées à cette catégorie.
     *
     * @return int
     */
    public function getFormationsCount(): int
    {
        return $this->formations->count();
    }
}
