<?php

namespace App\Entity;

use App\Repository\BaseCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BaseCategoryRepository::class)
 */
class BaseCategory
{
    /**
     * @Groups({"BC_all", "BC_mini", "MC_all", "MC_mini","menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"BC_all", "BC_mini", "MC_all", "MC_mini","menu_all"})
     * @ORM\Column(type="string", length=30)
     */
    private $nameEn;

    /**
     * @Groups({"BC_all", "BC_mini", "MC_all", "MC_mini","menu_all"})
     * @ORM\Column(type="string", length=30)
     */
    private $nameFr;

    /**
     * @Groups({"BC_all", "BC_mini", "MC_all", "MC_mini","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @Groups({"BC_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"BC_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"BC_all"})
     * @ORM\Column(type="boolean")
     */
    private $isDrink;

    /**
     * @Groups({"BC_all"})
     * @ORM\Column(type="boolean")
     */
    private $isFastFood;

    /**
     * BaseCategory constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): self
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(string $nameFr): self
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getIsDrink(): ?bool
    {
        return $this->isDrink;
    }

    public function setIsDrink(bool $isDrink): self
    {
        $this->isDrink = $isDrink;

        return $this;
    }

    public function getIsFastFood(): ?bool
    {
        return $this->isFastFood;
    }

    public function setIsFastFood(bool $isFastFood): self
    {
        $this->isFastFood = $isFastFood;

        return $this;
    }
}
