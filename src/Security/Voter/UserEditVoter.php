<?php

namespace App\Security\Voter;

use App\Entity\User;
use League\Bundle\OAuth2ServerBundle\Security\Authentication\Token\OAuth2Token;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEditVoter extends Voter
{


    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'EDIT' && $subject instanceof User;
    }

    /**
     * @param User $subject
     * @param OAuth2Token $token
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if (!$this->security->isGranted('OAUTH2_API')) {
            return false;
        }

        return $user === $subject;
    }
}
