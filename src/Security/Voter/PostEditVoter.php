<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostEditVoter extends Voter
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'EDIT'
            && $subject instanceof Post;
    }

    /**
     * @param Post $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $user === $subject->getAuthor();
    }
}
