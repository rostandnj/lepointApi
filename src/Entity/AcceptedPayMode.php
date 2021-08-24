<?php

namespace App\Entity;

use App\Repository\AcceptedPayModeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AcceptedPayModeRepository::class)
 */
class AcceptedPayMode
{
    /**
     * @Groups({"APM_all", "APM_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"APM_all", "APM_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $detailOne;

    /**
     * @Groups({"APM_all", "APM_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $detailTwo;


    /**
     * @Groups({"APM_all", "APM_mini","entity_all","entity_mini","menu_all"})
     * @ORM\ManyToOne(targetEntity=PayMode::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $payMode;

    /**
     * @Groups({"APM_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"APM_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity=Entity::class, inversedBy="acceptedPayModes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    /**
     * AcceptedPayMode constructor.
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

    public function getDetailOne(): ?string
    {
        return $this->detailOne;
    }

    public function setDetailOne(string $detailOne): self
    {
        $this->detailOne = $detailOne;

        return $this;
    }

    public function getDetailTwo(): ?string
    {
        return $this->detailTwo;
    }

    public function setDetailTwo(string $detailTwo): self
    {
        $this->detailTwo = $detailTwo;

        return $this;
    }


    public function getPayMode(): ?PayMode
    {
        return $this->payMode;
    }

    public function setPayMode(?PayMode $payMode): self
    {
        $this->payMode = $payMode;

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

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): self
    {
        $this->entity = $entity;

        return $this;
    }
}
