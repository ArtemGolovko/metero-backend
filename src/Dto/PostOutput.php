<?php

namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

class PostOutput
{
    /**
     * @Groups("post:read")
     */
    public int $id;

    /**
     * @Groups("post:read")
     */
    public string $body;

    /**
     * @Groups("post:read")
     */
    public User $author;

    /**
     * @Groups("post:read")
     */
    public int $likes;

    /**
     * @Groups("post:read")
     */
    public bool $isLiked;

    /**
     * @Groups("post:read")
     */
    public \DateTimeImmutable $createdAt;

    /**
     * @Groups("post:read")
     */
    public string $createdAtDiff;
}
