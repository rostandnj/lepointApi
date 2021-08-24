<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrderItemRepository::class)
 */
class OrderItem
{
    /**
     * @Groups({"OI_all", "OI_mini","order_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"OI_all", "OI_mini","order_all"})
     * @ORM\ManyToOne(targetEntity=Menu::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $menu;

    /**
     * @Groups({"OI_all", "OI_mini","order_all"})
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @Groups({"OI_all", "OI_mini","order_all"})
     * @ORM\Column(type="integer")
     */
    private $amount;

    /**
     * @Groups({"OI_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"OI_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $orderMain;

    /**
     * OrderItem constructor.
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

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getOrderMain(): ?Order
    {
        return $this->orderMain;
    }

    public function setOrderMain(?Order $orderMain): self
    {
        $this->orderMain = $orderMain;

        return $this;
    }
}
