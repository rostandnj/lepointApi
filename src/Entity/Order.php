<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="order_entity")
 */
class Order
{
    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $customerName;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $customerPhone;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\ManyToOne(targetEntity=Location::class,cascade={"persist"})
     */
    private $location;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $place;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @Groups({"order_all", "order_mini"})
     * @ORM\ManyToOne(targetEntity=PayMode::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $payMode;

    /**
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     */
    private $client;

    /**
     * @Groups({"order_all"})
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="orderMain",cascade={"persist"})
     */
    private $orderItems;

    /**
     * @Groups({"order_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"order_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;



    /**
     * @Gedmo\Slug(fields={"slug"})
     * @Groups({"order_all", "order_mini","status_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $reference;

    /**
     *
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @Groups({"order_all"})
     * @ORM\ManyToOne(targetEntity=Entity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    const STATUS_NEW = 0;
    const STATUS_PAID_ONLY = 1;
    const STATUS_PAID_AND_DELIVERED = 2;
    const STATUS_CANCELLED = 10;

    function getsum($n)
    {
        $sum = 0;
        while ($n != 0)
        {
            $sum = $sum + $n % 10;
            $n = $n/10;
        }
        return $sum;
    }

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->status = self::STATUS_NEW;
        $year =
            // somme des chiffres de l'année+mois+jour
        $this->slug = 'CM-'.$this->getsum((int)$this->date->format('Y')).
            $this->date->format('m').
            $this->date->format('d').$this->date->format('h').
            $this->date->format('i').
            $this->date->format('s');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getPayMode(): ?PayMode
    {
        return $this->payMode;
    }

    public function setPayMode(?PayMode $payMode): self
    {
        $this->payMode = $payMode;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrderMain($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderMain() === $this) {
                $orderItem->setOrderMain(null);
            }
        }

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


    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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
