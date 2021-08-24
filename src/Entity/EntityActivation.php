<?php

namespace App\Entity;

use App\Repository\EntityActivationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EntityActivationRepository::class)
 */
class EntityActivation
{
    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $startDate;

    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $endDate;

    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Column(type="string", length=20)
     */
    private $year;

    /**
     * @Groups({"EA_all", "EA_mini","entity_all"})
     * @ORM\Column(type="integer")
     */
    private $entityId;

    /**
     * @Groups({"EA_all","entity_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"EA_all","entity_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"EA_all","entity_all"})
     */
    private $isValid;



    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }


    public function getEntityId(): int
    {
        return $this->entityId;
    }


    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;
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


    public function getIsValid()
    {
        return (new \DateTime()) < $this->endDate;
    }


    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;
        return $this;
    }


}
