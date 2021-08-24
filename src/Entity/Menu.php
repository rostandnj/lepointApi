<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 */
class Menu
{
    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini", "order_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini", "order_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * @Groups({"menu_all"})
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Groups({"menu_all", "menu_mini"})
     * @ORM\Column(type="boolean")
     */
    private $isAvailable;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini", "order_all"})
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\Column(type="boolean")
     */
    private $isDayMenu;

    /**
     * @Groups({"menu_all", "menu_mini"})
     * @ORM\Column(type="integer")
     */
    private $nbOrder;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\Column(type="string", length=50)
     */
    private $estimateMinTime;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\Column(type="string", length=50)
     */
    private $estimateMaxTime;

    /**
     * @Groups({"menu_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"menu_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"menu_all"})
     * @ORM\ManyToMany(targetEntity=MenuCategory::class, inversedBy="menus",cascade={"persist"})
     */
    private $menuCategories;

    /**
     *  @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\Column(type="boolean")
     */
    private $isPromoted;

    /**
     * @Groups({"menu_all", "menu_mini", "OI_all", "OI_mini"})
     * @ORM\ManyToOne(targetEntity=Entity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    public function __construct()
    {
        $this->menuCategories = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->isAvailable = true;
        $this->isDayMenu = true;
        $this->nbOrder = 0;
        $this->isPromoted = false;
        $this->description = "";
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIsDayMenu(): ?bool
    {
        return $this->isDayMenu;
    }

    public function setIsDayMenu(bool $isDayMenu): self
    {
        $this->isDayMenu = $isDayMenu;

        return $this;
    }

    public function getNbOrder(): ?int
    {
        return $this->nbOrder;
    }

    public function setNbOrder(int $nbOrder): self
    {
        $this->nbOrder = $nbOrder;

        return $this;
    }

    public function getEstimateMinTime(): ?string
    {
        return $this->estimateMinTime;
    }

    public function setEstimateMinTime(string $estimateMinTime): self
    {
        $this->estimateMinTime = $estimateMinTime;

        return $this;
    }

    public function getEstimateMaxTime(): ?string
    {
        return $this->estimateMaxTime;
    }

    public function setEstimateMaxTime(string $estimateMaxTime): self
    {
        $this->estimateMaxTime = $estimateMaxTime;

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

    /**
     * @return Collection|MenuCategory[]
     */
    public function getMenuCategories(): Collection
    {
        return $this->menuCategories;
    }

    public function addMenuCategory(MenuCategory $menuCategory): self
    {
        if (!$this->menuCategories->contains($menuCategory)) {
            $this->menuCategories[] = $menuCategory;
        }

        return $this;
    }

    public function removeMenuCategory(MenuCategory $menuCategory): self
    {
        $this->menuCategories->removeElement($menuCategory);

        return $this;
    }

    public function getIsPromoted(): ?bool
    {
        return $this->isPromoted;
    }

    public function setIsPromoted(bool $isPromoted): self
    {
        $this->isPromoted = $isPromoted;

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
