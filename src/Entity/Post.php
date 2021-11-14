<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Dto\PostOutput;
use App\Dto\PostInput;

/**
 * @ApiResource(
 *     input=PostInput::class,
 *     output=PostOutput::class,
 *     normalizationContext={"groups": "post:read"},
 *     denormalizationContext={"groups": "post:write"},
 *     collectionOperations={
 *         "get"={
 *              "security"="is_granted('OAUTH2_POST_READ')",
 *         },
 *         "post"={
 *              "security"="is_granted('OAUTH2_POST_CREATE')",
 *         }
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('OAUTH2_POST_READ')",
 *          },
 *          "put"={
 *              "security"="is_granted('UPDATE', object)",
 *          },
 *          "delete"={
 *              "security"="is_granted('UPDATE', object)",
 *          },
 *          "patch"={
 *              "security"="is_granted('DELETE', object)",
 *          },
 *     }
 * )
 * @ApiFilter(SearchFilter::class, properties={"author": "exact"})
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull()
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="likedPosts")
     */
    private $likes;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(User $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
        }

        return $this;
    }

    public function removeLike(User $like): self
    {
        $this->likes->removeElement($like);

        return $this;
    }
}
