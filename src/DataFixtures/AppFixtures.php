<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\Model\Grant;
use League\Bundle\OAuth2ServerBundle\Model\RedirectUri;
use League\Bundle\OAuth2ServerBundle\Model\Scope;
use League\Bundle\OAuth2ServerBundle\OAuth2Grants;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;
    private UserDataProvider $dataProvider;

    public function __construct(UserPasswordHasherInterface $passwordHasher, UserDataProvider $dataProvider)
    {
        $this->passwordHasher = $passwordHasher;
        $this->dataProvider = $dataProvider;
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dataProvider->getUserData() as $userData) {
            $user = new User();
            $user
                ->setName($userData[0])
                ->setUsername($userData[1])
                ->setEmail($userData[2])
                ->setPassword($this->passwordHasher->hashPassword($user, $userData[3]))
            ;

            $manager->persist($user);
        }

        $client = new Client('Documentation Client', 'documentation_client', 'documentation_client_secret');
        $client
            ->setScopes(new Scope('api'))
            ->setActive(true)
            ->setGrants(new Grant(OAuth2Grants::AUTHORIZATION_CODE), new Grant(OAuth2Grants::REFRESH_TOKEN))
            ->setRedirectUris(new RedirectUri('https://127.0.0.1:8000/bundles/apiplatform/swagger-ui/oauth2-redirect.html'))
            ->setAllowPlainTextPkce(false)
        ;

        $manager->persist($client);

        $testClient = new Client('Test Client', 'test', '123');
        $testClient
            ->setScopes(new Scope('api'))
            ->setActive(true)
            ->setGrants(new Grant(OAuth2Grants::AUTHORIZATION_CODE), new Grant(OAuth2Grants::REFRESH_TOKEN))
            ->setRedirectUris(new RedirectUri('https://oauth.pstmn.io/v1/browser-callback'))
            ->setAllowPlainTextPkce(false)
        ;

        $manager->persist($testClient);

        $manager->flush();
    }
}
