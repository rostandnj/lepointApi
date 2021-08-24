<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LocationRepository::class)
 */
class Location
{
    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all"})
     * @ORM\ManyToOne(targetEntity=Country::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all","menu_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $streetDetail;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mapLink;

    /**
     * @Groups({"location_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"location_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all","menu_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @Groups({"location_all", "location_mini", "user_all", "entity_all","order_all", "order_mini","menu_all","menu_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * Location constructor.
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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getStreetDetail(): ?string
    {
        return $this->streetDetail;
    }

    public function setStreetDetail(string $streetDetail): self
    {
        $this->streetDetail = $streetDetail;

        return $this;
    }

    public function getMapLink(): ?string
    {
        return $this->mapLink;
    }

    public function setMapLink(string $mapLink): self
    {
        $this->mapLink = $mapLink;

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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }
}
