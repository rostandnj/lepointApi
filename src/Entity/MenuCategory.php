<?php

namespace App\Entity;

use App\Repository\MenuCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MenuCategoryRepository::class)
 */
class MenuCategory
{
    /**
     * @Groups({"MC_all", "MC_mini", "menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"MC_all", "MC_mini", "menu_all"})
     * @ORM\ManyToOne(targetEntity=BaseCategory::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $baseCategory;



    /**
     * @Groups({"MC_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"MC_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToMany(targetEntity=Menu::class, mappedBy="menuCategories")
     */
    private $menus;

    /**
     * @ORM\ManyToOne(targetEntity=Entity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseCategory(): ?BaseCategory
    {
        return $this->baseCategory;
    }

    public function setBaseCategory(?BaseCategory $baseCategory): self
    {
        $this->baseCategory = $baseCategory;

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
     * @return Collection|Menu[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->addMenuCategory($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            $menu->removeMenuCategory($this);
        }

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
