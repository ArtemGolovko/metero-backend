<?php

namespace App\Security\Voter;

use League\Bundle\OAuth2ServerBundle\Security\Authentication\Token\OAuth2Token;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuth2Voter extends Voter
{

    private string $prefix;

    private string $role_prefix;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->prefix = $parameterBag->get('oauth2_prefix');
        $this->role_prefix = $parameterBag->get('oauth2_role_prefix');
    }

    protected function supports(string $attribute, $subject): bool
    {
        return str_starts_with($attribute, $this->prefix);
    }

    /**
     * @param OAuth2Token $token
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if (!$token instanceof OAuth2Token) {
            return false;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $scope = $this->getScope($attribute);

        return in_array($this->role_prefix.$scope, $token->getRoleNames());
    }

    private function getScope(string $attribute): string
    {
        return strtoupper(substr($attribute, strlen($this->prefix)));
    }
}
