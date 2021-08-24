<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @Groups({"notif_all", "notif_mini","status_all","status_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"notif_all", "notif_mini","status_all","status_mini"})
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @Groups({"notif_all", "notif_mini", "status_all"})
     * @ORM\ManyToOne(targetEntity=Order::class,cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $orderMain;

    /**
     * @Groups({"notif_all","status_all","status_mini"})
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @Groups({"notif_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"notif_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity=Status::class, mappedBy="notification",cascade={"persist"})
     */
    private $statuses;

    /**
     * @Groups({"notif_all","status_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $customerName;

    /**
     * @Groups({"notif_all","status_all"})
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $customerPhone;

    const ORDER_INTERNAL_WITH_ACCOUNT = 10;
    const ORDER_INTERNAL_WITHOUT_ACCOUNT = 11;
    const ORDER_EXTERNAL_WITH_ACCOUNT = 20;
    const ORDER_EXTERNAL_WITHOUT_ACCOUNT = 21;

    public function __construct()
    {
        $this->statuses = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getOrderMain(): ?Order
    {
        return $this->orderMain;
    }

    public function setOrderMain(?Order $orderMain): self
    {
        $this->orderMain = $orderMain;

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

    /**
     * @return Collection|Status[]
     */
    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function addStatus(Status $status): self
    {
        if (!$this->statuses->contains($status)) {
            $this->statuses[] = $status;
            $status->setNotification($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): self
    {
        if ($this->statuses->removeElement($status)) {
            // set the owning side to null (unless already changed)
            if ($status->getNotification() === $this) {
                $status->setNotification(null);
            }
        }

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(?string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(?string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }
}
