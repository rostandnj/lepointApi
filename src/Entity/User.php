<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all","comment_all","comment_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all","comment_all","comment_mini"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all","comment_all","comment_mini"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $surname;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all","comment_all","comment_mini"})
     * @ORM\Column(type="string", length=35)
     */
    private $email;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all"})
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $phone;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all"})
     * @ORM\Column(type="integer", nullable=false)
     */
    private $type;

    /**
     * @Groups({"user_all", "user_mini","status_all"})
     * @ORM\ManyToOne(targetEntity=Location::class,cascade={"persist"})
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rpassword;

    /**
     * @Groups({"user_all"})
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"user_all"})
     * @ORM\Column(type="boolean")
     */
    private $isClose;

    /**
     * @Groups({"user_all"})
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=Order::class, mappedBy="client")
     */
    private $orders;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    private $token;

    /**
     * @Groups({"user_all", "user_mini", "order_all", "order_mini","status_all", "entity_all","comment_all","comment_mini"})
     * @ORM\Column(name="picture", type="string",nullable=false)
     */
    private $picture;

    /**
     * @Groups({"user_all", "user_mini","status_all"})
     * @ORM\Column(type="integer")
     */
    private $gender;

    /**
     * @Groups({"user_all", "user_mini"})
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $activationDate;

    /**
     * @ORM\ManyToMany(targetEntity=Entity::class, inversedBy="managers")
     */
    private $managerEntities;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="user")
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity=Reaction::class, mappedBy="user")
     */
    private $reactions;

    CONST USER_ADMIN=0;
    CONST USER_TOP_MANAGER=1;
    CONST USER_OWNER=10;
    CONST USER_MANAGER=11;
    CONST USER_CLIENT=20;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = false;
        $this->isClose = false;
        $this->roles = [];
        $this->token = uniqid('',true);
        $this->picture = $this->gender == 1 ? 'man.png':'woman.png';
        $this->topManagerRestaurants = new ArrayCollection();
        $this->managerEntities = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->reactions = new ArrayCollection();
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

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

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRpassword(): ?string
    {
        return $this->rpassword;
    }

    public function setRpassword(string $rpassword): self
    {
        $this->rpassword = $rpassword;

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

    public function getIsClose(): ?bool
    {
        return $this->isClose;
    }

    public function setIsClose(bool $isClose): self
    {
        $this->isClose = $isClose;

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
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setClient($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getClient() === $this) {
                $order->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @param mixed $token
     *
     * @return User
     */
    public function setToken( $token ) {
        $this->token = $token;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     * @return User
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return [$this->type];
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        return "";
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(?\DateTimeInterface $activationDate): self
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    /**
     * @return Collection|Entity[]
     */
    public function getManagerEntities(): Collection
    {
        return $this->managerEntities;
    }

    public function addManagerEntity(Entity $managerEntity): self
    {
        if (!$this->managerEntities->contains($managerEntity)) {
            $this->managerEntities[] = $managerEntity;
        }

        return $this;
    }

    public function removeManagerEntity(Entity $managerEntity): self
    {
        $this->managerEntities->removeElement($managerEntity);

        return $this;
    }

    public function toArray(){
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'surname'=>$this->surname,
            'email'=>$this->email,
            'pivture'=>$this->picture,

        ];
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setUser($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getUser() === $this) {
                $article->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reaction[]
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setUser($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction)) {
            // set the owning side to null (unless already changed)
            if ($reaction->getUser() === $this) {
                $reaction->setUser(null);
            }
        }

        return $this;
    }

}
