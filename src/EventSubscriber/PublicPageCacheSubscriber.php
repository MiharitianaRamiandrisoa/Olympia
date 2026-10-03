<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PublicPageCacheSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $route = (string) $request->attributes->get('_route');

        if (!in_array($request->getMethod(), ['GET', 'HEAD'], true)
            || $response->getStatusCode() !== 200
            || !str_starts_with($route, 'app_')
            || str_starts_with($route, 'app_admin_')
            || str_starts_with($route, 'app_admin')) {
            return;
        }

        $response->setPublic();
        $response->setMaxAge(60);
        $response->setSharedMaxAge(60);
        $response->headers->set('Cache-Control', 'public, max-age=60, s-maxage=60, stale-while-revalidate=300');
    }
}
