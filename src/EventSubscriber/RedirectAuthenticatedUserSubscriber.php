<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectAuthenticatedUserSubscriber implements EventSubscriberInterface
{
    private const array ROUTES_TO_REDIRECT = [
        'app_login',
        'app_register',
    ];

    public function __construct(
        private readonly Security     $security,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        // Check if user is authenticated and trying to access login or register routes
        if ($this->security->getUser() && in_array($routeName, self::ROUTES_TO_REDIRECT)) {
            $event->setResponse(
                new RedirectResponse($this->urlGenerator->generate('app_dashboard'))
            );
        }
    }
}
