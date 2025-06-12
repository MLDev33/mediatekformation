<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité représentant une formation, liée à une playlist et à plusieurs catégories.
 *
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{

    /**
     * Base de l'URL utilisée pour générer les miniatures vidéo YouTube.
     */
    private const TEMPLATE_DETAIL_IMAGE = "https://i.ytimg.com/vi/";

    /**
     * Identifiant unique auto-généré de la formation.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Date de publication de la formation.
     *
     * La date ne peut pas être postérieure à aujourd’hui.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual("today", message: "La date ne peut pas être postérieure à aujourd'hui.")]
    private ?\DateTimeInterface $publishedAt = null;

    /**
     * Titre de la formation.
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $title = null;

    /**
     * Description détaillée de la formation.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Identifiant de la vidéo YouTube associée à la formation.
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $videoId = null;

    /**
     * Playlist à laquelle appartient cette formation.
     */
    #[ORM\ManyToOne(inversedBy: 'formations')]
    private ?Playlist $playlist = null;

    /**
     * Catégories associées à cette formation.
     *
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'formations')]
    private Collection $categories;

    /**
     * Constructeur.
     *
     * Initialise la collection des catégories.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique de la formation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère la date de publication.
     */
    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    /**
     * Définit la date de publication.
     *
     * @param \DateTimeInterface|null $publishedAt
     * @return static
     */
    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Récupère la date de publication au format chaîne (jj/mm/aaaa).
     */
    public function getPublishedAtString(): string
    {
        if ($this->publishedAt == null) {
            return "";
        }
        return $this->publishedAt->format('d/m/Y');
    }

    /**
     * Récupère le titre de la formation.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Définit le titre de la formation.
     *
     * @param string|null $title
     * @return static
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Récupère la description de la formation.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description de la formation.
     *
     * @param string|null $description
     * @return static
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Récupère l’identifiant de la vidéo.
     */
    public function getVideoId(): ?string
    {
        return $this->videoId;
    }

    /**
     * Définit l’identifiant de la vidéo.
     *
     * @param string|null $videoId
     * @return static
     */
    public function setVideoId(?string $videoId): static
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Récupère l'URL de la miniature par défaut de la vidéo (taille normale).
     *
     * @return string|null
     */
    public function getMiniature(): ?string
    {
        return $this->videoId ? self::TEMPLATE_DETAIL_IMAGE . $this->videoId . "/default.jpg" : null;
    }

    /**
     * Récupère l'URL de la miniature haute qualité de la vidéo.
     *
     * @return string|null
     */
    public function getPicture(): ?string
    {
        return $this->videoId ? self::TEMPLATE_DETAIL_IMAGE . $this->videoId . "/hqdefault.jpg" : null;
    }

    /**
     * Récupère la playlist associée à cette formation.
     */
    public function getPlaylist(): ?playlist
    {
        return $this->playlist;
    }

    /**
     * Définit la playlist associée à cette formation.
     *
     * @param Playlist|null $playlist
     * @return static
     */
    public function setPlaylist(?Playlist $playlist): static
    {
        $this->playlist = $playlist;

        return $this;
    }

    /**
     * Récupère les catégories associées à la formation.
     *
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * Ajoute une catégorie à cette formation.
     *
     * @param Categorie $category
     * @return $this
     */
    public function addCategory(Categorie $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * Supprime une catégorie de cette formation.
     *
     * @param Categorie $category
     * @return $this
     */
    public function removeCategory(Categorie $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }
}
