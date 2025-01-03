<?php

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    /**
     * @var Collection<int, Movies>
     */
    #[ORM\ManyToMany(targetEntity: Movies::class, mappedBy: 'categories')]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    /**
     * Get the ID of the category
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title of the category
     *
     * @return string|null
     */
    public function getTitre(): ?string
    {
        return $this->titre;
    }

    /**
     * Set the title of the category
     *
     * @param string $titre
     * @return static
     */
    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get all movies associated with this category
     *
     * @return Collection<int, Movies>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    /**
     * Add a movie to this category
     *
     * @param Movies $movie
     * @return static
     */
    public function addMovie(Movies $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->addCategory($this);
        }

        return $this;
    }

    /**
     * Remove a movie from this category
     *
     * @param Movies $movie
     * @return static
     */
    public function removeMovie(Movies $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            $movie->removeCategory($this);
        }

        return $this;
    }
}
