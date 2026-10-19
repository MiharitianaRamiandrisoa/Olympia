<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticationSessionSubscriber implements EventSubscriberInterface
{
    private const LAST_ACTIVITY_KEY = 'olympia.auth.last_activity';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly int $sessionIdleTimeout,
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
        if (!$event->isMainRequest() || $this->sessionIdleTimeout <= 0) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof UserInterface) {
            return;
        }

        $session = $request->getSession();
        $now = time();
        $lastActivity = (int) $session->get(self::LAST_ACTIVITY_KEY, 0);

        if ($lastActivity > 0 && ($now - $lastActivity) > $this->sessionIdleTimeout) {
            $this->tokenStorage->setToken(null);
            $session->invalidate();

            $loginRoute = str_starts_with($request->getPathInfo(), '/olympia-admin')
                ? 'app_admin_login'
                : 'app_login';
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate($loginRoute)));

            return;
        }

        $session->set(self::LAST_ACTIVITY_KEY, $now);
    }
}
