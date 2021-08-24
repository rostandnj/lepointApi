<?php

namespace App\Entity;

use App\Repository\AdvertRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=AdvertRepository::class)
 */
class Advert
{
    /**
     * @Groups({"adv_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $file = [];

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"adv_all"})
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @Groups({"adv_all"})
     * @ORM\ManyToOne(targetEntity=Entity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    /**
     * @Groups({"adv_all"})
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * Advert constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->status = true;
        $this->isActive = true;
        $this->file = null;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getFile(): ?array
    {
        return $this->file;
    }

    public function setFile(?array $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
    
}
