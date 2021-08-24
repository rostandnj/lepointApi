<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @Groups({"file_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;


    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="string", length=5)
     */
    private $extension;

    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $path;


    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"file_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Entity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $entity;




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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

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
