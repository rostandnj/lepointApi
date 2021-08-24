<?php

namespace App\Entity;

use App\Repository\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EntityRepository::class)
 */
class Entity
{
    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all","adv_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all"})
     * @ORM\OneToOne(targetEntity=GlobalInfo::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $globalInfo;

    /**
     * @Groups({"entity_all","entity_mini","order_all","menu_all"})
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @Groups({"entity_all","entity_mini","product_all","menu_all","order_all"})
     * @ORM\OneToOne(targetEntity=Location::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    /**
     * @Groups({"entity_all","entity_mini","menu_all"})
     * 0 = no order , 1 = display only item, 2 = order
     * @ORM\Column(type="integer")
     */
    private $orderSystem;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="string", length=100)
     */
    private $orderEntity;

    /**
     * @Groups({"entity_all","entity_mini","menu_all"})
     * @ORM\Column(type="boolean")
     */
    private $canOrder;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="text")
     */
    private $flashMessage;

    /**
     * @Groups({"entity_all","entity_mini","menu_all","order_all"})
     * @ORM\Column(type="integer")
     */
    private $note;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"entity_all","entity_mini","order_all"})
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     *  @Groups({"entity_all","entity_mini"})
     * @ORM\ManyToOne(targetEntity=User::class, cascade={"persist"})
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="managerEntities", cascade={"persist"})
     */
    private $managers;

    /**
     *  @Groups({"entity_all","entity_mini"})
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $topManager;

    /**
     * @Groups({"entity_all","entity_mini","menu_all"})
     * @ORM\OneToMany(targetEntity=AcceptedPayMode::class, mappedBy="entity", orphanRemoval=true, cascade={"persist"})
     */
    private $acceptedPayModes;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="boolean")
     */
    private $isLock;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\ManyToOne(targetEntity=EntityActivation::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $entityActivation;

    /**
     * @Groups({"entity_all","entity_mini"})
     * @ORM\Column(type="integer")
     */
    private $isPromoted;

    const STATUS_NOT_PAID = 0;
    const STATUS_PAID = 1;

    const ENTITY_RESTAURANT = 1;
    const ENTITY_LAUNCH = 2;
    const ENTITY_DISCO = 3;
    const ENTITY_HOSTEL = 4;
    const ENTITY_MUSEUM = 5;
    const ENTITY_OFFICE = 6;
    const ENTITY_PHARMACY = 7;

    const ENTITY_USE_MENU = [Entity::ENTITY_RESTAURANT, Entity::ENTITY_LAUNCH, Entity::ENTITY_DISCO];


    /**
     * Entity constructor.
     */
    public function __construct()
    {
        $this->isActive = true;
        $this->flashMessage = "";
        $this->status = self::STATUS_NOT_PAID;
        $this->note = 0;
        $this->canOrder = false;
        $this->orderEntity = "";
        $this->managers = new ArrayCollection();
        $this->acceptedPayModes = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isLock = false;
        $this->isPromoted = 0;
        $this->orderSystem = 1;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGlobalInfo(): ?GlobalInfo
    {
        return $this->globalInfo;
    }

    public function setGlobalInfo(GlobalInfo $globalInfo): self
    {
        $this->globalInfo = $globalInfo;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getOrderSystem(): ?int
    {
        return $this->orderSystem;
    }

    public function setOrderSystem(int $orderSystem): self
    {
        $this->orderSystem = $orderSystem;

        return $this;
    }

    public function getOrderEntity(): ?string
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(string $orderEntity): self
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }

    public function getCanOrder(): ?bool
    {
        return $this->canOrder;
    }

    public function setCanOrder(bool $canOrder): self
    {
        $this->canOrder = $canOrder;

        return $this;
    }

    public function getFlashMessage(): ?string
    {
        return $this->flashMessage;
    }

    public function setFlashMessage(string $flashMessage): self
    {
        $this->flashMessage = $flashMessage;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(User $manager): self
    {
        if (!$this->managers->contains($manager)) {
            $this->managers[] = $manager;
            $manager->addManagerEntity($this);
        }

        return $this;
    }

    public function removeManager(User $manager): self
    {
        if ($this->managers->removeElement($manager)) {
            $manager->removeManagerEntity($this);
        }

        return $this;
    }

    public function getTopManager(): ?User
    {
        return $this->topManager;
    }

    public function setTopManager(?User $topManager): self
    {
        $this->topManager = $topManager;

        return $this;
    }
    

    /**
     * @return Collection|AcceptedPayMode[]
     */
    public function getAcceptedPayModes(): Collection
    {
        return $this->acceptedPayModes;
    }

    public function addAcceptedPayMode(AcceptedPayMode $acceptedPayMode): self
    {
        if (!$this->acceptedPayModes->contains($acceptedPayMode)) {
            $this->acceptedPayModes[] = $acceptedPayMode;
            $acceptedPayMode->setEntity($this);
        }

        return $this;
    }

    public function removeAcceptedPayMode(AcceptedPayMode $acceptedPayMode): self
    {
        if ($this->acceptedPayModes->removeElement($acceptedPayMode)) {
            // set the owning side to null (unless already changed)
            if ($acceptedPayMode->getEntity() === $this) {
                $acceptedPayMode->setEntity(null);
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

    public function getIsLock(): ?bool
    {
        return $this->isLock;
    }

    public function setIsLock(bool $isLock): self
    {
        $this->isLock = $isLock;

        return $this;
    }

    public function getEntityActivation(): ?EntityActivation
    {
        return $this->entityActivation;
    }

    public function setEntityActivation(?EntityActivation $entityActivation): self
    {
        $this->entityActivation = $entityActivation;

        return $this;
    }

    public function getIsPromoted(): ?int
    {
        return $this->isPromoted;
    }

    public function setIsPromoted(int $isPromoted): self
    {
        $this->isPromoted = $isPromoted;

        return $this;
    }

    public function updateCanOrder(): self
    {
        $this->canOrder = !$this->canOrder;

        return $this;
    }
}
