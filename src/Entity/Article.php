<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="string",length=200)
     */
    private $title;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="integer")
     */
    private $rate;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="integer")
     */
    private $nbView;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="array")
     */
    private $tags = [];

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="article",cascade={"persist","remove"})
     */
    private $comments;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=Reaction::class, mappedBy="article")
     */
    private $reactions;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageCover;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="integer")
     */
    private $nbComment;

    /**
     * @Groups({"article_all","article_mini"})
     * @ORM\Column(type="integer")
     */
    private $type;

    const TYPE_GENERAL = 0;
    const TYPE_COCAN = 10;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->date = new \DateTime();
        $this->tags = [];
        $this->rate = 0;
        $this->isActive = true;
        $this->nbView = 0;
        $this->nbComment = 0;
        $this->type = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getNbView(): ?int
    {
        return $this->nbView;
    }

    public function setNbView(int $nbView): self
    {
        $this->nbView = $nbView;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

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
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
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
            $reaction->setArticle($this);
        }

        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction)) {
            // set the owning side to null (unless already changed)
            if ($reaction->getArticle() === $this) {
                $reaction->setArticle(null);
            }
        }

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

    public function getImageCover(): ?string
    {
        return $this->imageCover;
    }

    public function setImageCover(?string $imageCover): self
    {
        $this->imageCover = $imageCover;

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

    public function incNbView(): self
    {
        $this->nbView++;

        return $this;
    }

    public function incNbComment(): self
    {
        $this->nbComment++;

        return $this;
    }

    public function getNbComment(): ?int
    {
        return $this->nbComment;
    }

    public function setNbComment(int $nbComment): self
    {
        $this->nbComment = $nbComment;

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
}
