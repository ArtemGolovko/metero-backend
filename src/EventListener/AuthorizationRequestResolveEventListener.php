<?php

namespace App\EventListener;

use League\Bundle\OAuth2ServerBundle\Event\AuthorizationRequestResolveEvent;
use League\Bundle\OAuth2ServerBundle\OAuth2Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthorizationRequestResolveEventListener implements EventSubscriberInterface
{

    public const SESSION_AUTHORIZATION_RESULT = '_app.oauth2.authorization_result';
    public const SESSION_AUTHORIZATION_QUERY = '_app.oauth2.authorization_query';
    private RequestStack $requestStack;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }


    public function onAuthorizationRequestResolveEvent(AuthorizationRequestResolveEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $this->requestStack->getSession();
        if ($session->has(self::SESSION_AUTHORIZATION_RESULT)) {
            $event->resolveAuthorization($session->get(self::SESSION_AUTHORIZATION_RESULT));
            $session->remove(self::SESSION_AUTHORIZATION_RESULT);
            $session->remove(self::SESSION_AUTHORIZATION_QUERY);

            return;
        }

        $session->set(self::SESSION_AUTHORIZATION_QUERY, $request->query->all());
        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('app_account_consent')
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OAuth2Events::AUTHORIZATION_REQUEST_RESOLVE => 'onAuthorizationRequestResolveEvent'
        ];
    }
}
