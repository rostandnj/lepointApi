<?php

namespace App\Entity;

use App\Repository\NightPharmacyRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NightPharmacyRepository::class)
 */
class NightPharmacy
{
    /**
     * @ORM\Id
     * @Groups({"phar_all"})
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"phar_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"phar_all"})
     *  @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"phar_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $writeDate;

    /**
     * @Groups({"phar_all"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @Groups({"phar_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Groups({"phar_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * NightPharmacy constructor.
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->date = new \DateTime();
        $this->writeDate = new \DateTime();
        $this->setContent('');

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getWriteDate(): ?\DateTimeInterface
    {
        return $this->writeDate;
    }

    public function setWriteDate(\DateTimeInterface $writeDate): self
    {
        $this->writeDate = $writeDate;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

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
}
