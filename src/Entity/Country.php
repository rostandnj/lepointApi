<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 */
class Country
{
    /**
     * @Groups({"country_all", "country_mini", "location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"country_all", "country_mini", "location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all"})
     * @ORM\Column(type="string", length=50)
     */
    private $nameEn;

    /**
     * @Groups({"country_all", "country_mini", "location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all"})
     * @ORM\Column(type="string", length=50)
     */
    private $nameFr;

    /**
     * @Groups({"country_all", "country_mini", "location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all"})
     * @ORM\Column(type="string", length=5)
     */
    private $code;

    /**
     * @Groups({"country_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"country_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * Country constructor.
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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
