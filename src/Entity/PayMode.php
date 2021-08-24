<?php

namespace App\Entity;

use App\Repository\PayModeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PayModeRepository::class)
 */
class PayMode
{
    /**
     * @Groups({"PM_all", "PM_mini", "APM_all", "APM_mini", "order_all", "order_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"PM_all", "PM_mini", "APM_all", "APM_mini", "order_all", "order_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @Groups({"PM_all", "PM_mini", "APM_all", "APM_mini", "order_all", "order_mini","entity_all","entity_mini","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @Groups({"PM_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"PM_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * PayMode constructor.
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
}
