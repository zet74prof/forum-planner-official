<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ForumRepository::class)]
class Forum
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    /**
     * @var Collection<int, Stand>
     */
    #[ORM\OneToMany(targetEntity: Stand::class, mappedBy: 'forum')]
    private Collection $stands;

    #[ORM\ManyToOne(inversedBy: 'forums')]
    private ?User $user = null;

    public function __construct()
    {
        $this->stands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Stand>
     */
    public function getStands(): Collection
    {
        return $this->stands;
    }

    public function addStand(Stand $stand): static
    {
        if (!$this->stands->contains($stand)) {
            $this->stands->add($stand);
            $stand->setForum($this);
        }

        return $this;
    }

    public function removeStand(Stand $stand): static
    {
        if ($this->stands->removeElement($stand)) {
            // set the owning side to null (unless already changed)
            if ($stand->getForum() === $this) {
                $stand->setForum(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
