<?php

namespace App\Entity;

use App\Repository\DayRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DayRepository::class)
 */
class Day
{
    /**
     * @Groups({"day_all", "day_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"day_all", "day_mini"})
     * @ORM\Column(type="string", length=25)
     */
    private $nameEn;

    /**
     * @Groups({"day_all", "day_mini"})
     * @ORM\Column(type="string", length=25)
     */
    private $nameFr;

    /**
     * @Groups({"day_all", "day_mini"})
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @Groups({"day_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"day_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * Day constructor.
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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

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
}
