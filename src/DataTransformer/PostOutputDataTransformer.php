<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Dto\PostOutput;
use App\Entity\Post;
use Symfony\Component\Security\Core\Security;

class PostOutputDataTransformer implements DataTransformerInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Post $object
     */
    public function transform($object, string $to, array $context = [])
    {
        $postOutput = new PostOutput();
        $postOutput->id = $object->getId();
        $postOutput->body = $object->getBody();
        $postOutput->author = $object->getAuthor();
        $postOutput->likes = $object->getLikes()->count();
        $postOutput->isLiked = $object->getLikes()->contains($this->security->getUser());
        return $postOutput;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return $data instanceof Post && $to === PostOutput::class;
    }
}
