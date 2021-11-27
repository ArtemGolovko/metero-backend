<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class PreflightRequestEventListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$event->getRequest()->getMethod() !== Request::METHOD_OPTIONS) {
            return;
        }

        $this->logger->info('Preflight request', [
            'request' => $event->getRequest(),
            'response' => $event->getResponse()
        ]);
        $this->logger->info('Is Cross Origin', [
            $this->isCrossOrigin($event->getRequest())
        ]);

        $request = $event->getRequest();
        $response = new Response();
        if ($this->isCrossOrigin($request)) {
            $response->headers->set('Access-Control-Allow-Origin', $request->getSchemeAndHttpHost());
        }
        $event->setResponse($response);
    }

    protected function isCrossOrigin(Request $request): bool
    {
        if (!$request->headers->has('Origin')) {
            return false;
        }

        return $request->getSchemeAndHttpHost() !== $request->headers->get('Origin');
    }

}
