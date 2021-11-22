<?php

namespace App\EventListener;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Repository\UserRepository;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use League\Bundle\OAuth2ServerBundle\Event\TokenRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TokenRequestResolveEventListener implements EventSubscriberInterface
{
    private ParameterBagInterface $parameterBag;
    private RequestStack $requestStack;
    private UserRepository $userRepository;
    private IriConverterInterface $iriConverter;
    private LoggerInterface $logger;

    public function __construct(
        ParameterBagInterface $parameterBag,
        RequestStack $requestStack,
        UserRepository $userRepository,
        IriConverterInterface $iriConverter,
        LoggerInterface $logger
    ) {
        $this->parameterBag = $parameterBag;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
        $this->iriConverter = $iriConverter;
        $this->logger = $logger;
    }

    public function onTokenRequestResolve(TokenRequestResolveEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $response = $event->getResponse();
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Failed to get access token', [
                'response' => $response
            ]);
            return;
        }

//        $response->headers->add([
//            'Access-Control-Allow-Origin' => 'http://10.0.2.15:3000'
//        ]);

        $json = json_decode($response->getContent(), true);
        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::file($this->parameterBag->get('private_key_path'), $this->parameterBag->get('private_key_passphrase')),
            InMemory::file($this->parameterBag->get('public_key_path'))
        );

        /** @var UnencryptedToken $accessToken */
        $accessToken = $configuration->parser()->parse($json['access_token']);

        $user = $this->userRepository->findOneBy(['username' => $accessToken->claims()->get('sub')]);

        $now = new \DateTimeImmutable();
        $idToken = $configuration
            ->builder()
            ->issuedBy('https://127.0.0.1:8000')
            ->permittedFor($request->request->get('client_id'))
            ->issuedAt($now)
            ->expiresAt($now->modify('+10 minutes'))
            ->relatedTo($this->iriConverter->getIriFromItem($user))
            ->withClaim('id', $user->getId())
            ->withClaim('username', $user->getUsername())
            ->withClaim('name', $user->getName())
            ->withClaim('email', $user->getEmail())
            ->getToken($configuration->signer(), $configuration->signingKey());

        $json['id_token'] = $idToken->toString();

        $response->setContent(json_encode($json));
    }

    public static function getSubscribedEvents()
    {
        return [
            OAuth2Events::TOKEN_REQUEST_RESOLVE => 'onTokenRequestResolve',
        ];
    }
}
