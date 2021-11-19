<?php

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\PostInput;
use App\Entity\Post;
use Symfony\Component\Security\Core\Security;

class PostInputDataTransformerInitializer implements DataTransformerInitializerInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param PostInput $object
     */
    public function transform($object, string $to, array $context = [])
    {
        /** @var Post $post */
        $post = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? new Post;
        $post->setBody($object->body);

        if ($object->isLiked) {
            $post->addLike($this->security->getUser());
        } else {
            $post->removeLike($this->security->getUser());
        }

        if (!$post->getAuthor()) {
            $post->setAuthor($this->security->getUser());
        }
        if (!$post->getCreatedAt()) {
            $post->setCreatedAt(new \DateTimeImmutable());
        }

        return $post;
    }

    /**
     * @inheritDoc
     */
    public function initialize(string $inputClass, array $context = [])
    {
        /** @var Post $post */
        $post = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;
        $postInput = new PostInput();
        if (!$post) {
            $postInput->body = '';
            $postInput->isLiked = false;
            return $postInput;
        }

        $postInput->body = $post->getBody();
        $postInput->isLiked = $post->getLikes()->contains($this->security->getUser());

        return $postInput;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof Post) {
            return false;
        }

        return Post::class === $to && null !== ($context['input']['class'] ?? null);
    }
}
