<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class PostInput
{
    /**
     * @Groups("post:write")
     */
    public string $body;

    /**
     * @Groups("post:write")
     */
    public bool $isLiked;
}
