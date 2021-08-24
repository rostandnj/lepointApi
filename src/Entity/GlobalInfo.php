<?php

namespace App\Entity;

use App\Repository\GlobalInfoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=GlobalInfoRepository::class)
 */
class GlobalInfo
{
    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all"})
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @Groups({"entity_all","entity_mini","order_all","menu_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @Groups({"entity_all","entity_mini","order_all","menu_all"})
     * @ORM\Column(type="string", length=30)
     */
    private $phone1;

    /**
     * @Groups({"entity_all","entity_mini","order_all","menu_all"})
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $phone2;

    /**
     * @Groups({"entity_all","entity_mini","order_all"})
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @Groups({"entity_all","entity_mini","order_all"})
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $whatsappPhone;

    /**
     * @Groups({"entity_all","entity_mini","order_all"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookPage;

    /**
     * @Groups({"entity_all","entity_mini","order_all","product_all","menu_all"})
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    /**
     * GlobalInfo constructor.
     */
    public function __construct()
    {
        $this->phone2 = "";
        $this->facebookPage = "";
        $this->whatsappPhone = "";
        $this->website = "";
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(string $phone1): self
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

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

    public function getWhatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function setWhatsappPhone(?string $whatsappPhone): self
    {
        $this->whatsappPhone = $whatsappPhone;

        return $this;
    }

    public function getFacebookPage(): ?string
    {
        return $this->facebookPage;
    }

    public function setFacebookPage(?string $facebookPage): self
    {
        $this->facebookPage = $facebookPage;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
}
