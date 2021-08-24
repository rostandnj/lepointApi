<?php

namespace App\Entity;

use App\Repository\OpenDayRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OpenDayRepository::class)
 */
class OpenDay
{
    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\Column(type="string", length=10)
     */
    private $startHourOne;

    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\Column(type="string", length=10)
     */
    private $endHourOne;

    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\Column(type="string", length=10)
     */
    private $startHourTwo;

    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\Column(type="string", length=10)
     */
    private $endHourTwo;

    /**
     * @Groups({"OD_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"OD_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"OD_all", "OD_mini","entity_all"})
     * @ORM\ManyToOne(targetEntity=Day::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $day;

    /**
     * OpenDay constructor.
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

    public function getStartHourOne(): ?string
    {
        return $this->startHourOne;
    }

    public function setStartHourOne(string $startHourOne): self
    {
        $this->startHourOne = $startHourOne;

        return $this;
    }

    public function getEndHourOne(): ?string
    {
        return $this->endHourOne;
    }

    public function setEndHourOne(string $endHourOne): self
    {
        $this->endHourOne = $endHourOne;

        return $this;
    }

    public function getStartHourTwo(): ?string
    {
        return $this->startHourTwo;
    }

    public function setStartHourTwo(string $startHourTwo): self
    {
        $this->startHourTwo = $startHourTwo;

        return $this;
    }

    public function getEndHourTwo(): ?string
    {
        return $this->endHourTwo;
    }

    public function setEndHourTwo(string $endHourTwo): self
    {
        $this->endHourTwo = $endHourTwo;

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

    public function getDay(): ?Day
    {
        return $this->day;
    }

    public function setDay(?Day $day): self
    {
        $this->day = $day;

        return $this;
    }
}
