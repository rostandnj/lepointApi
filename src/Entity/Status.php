<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=StatusRepository::class)
 */
class Status
{
    /**
     * @Groups({"status_all", "status_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"status_all", "status_mini"})
     * @ORM\ManyToOne(targetEntity=Notification::class, inversedBy="statuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $notification;

    /**
     * @Groups({"status_all", "status_mini"})
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @Groups({"status_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"status_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"status_all"})
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->status = 0;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    public function setNotification(?Notification $notification): self
    {
        $this->notification = $notification;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
