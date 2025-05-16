<?php
// src/EventSubscriber/ApiLoggerSubscriber.php
namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiLoggerSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (str_starts_with($path, '/api')) {
            $this->logger->info('Appel API', [
                'method'      => $request->getMethod(),
                'path'        => $path,
                'route'       => $request->attributes->get('_route'),
                'ip'          => $request->getClientIp(),
                'user'        => $request->getUser() ?? 'anonymous',
                'query'       => $request->query->all(),
                'request'     => $request->request->all(),
                'json'        => json_decode($request->getContent(), true),
                'headers'     => $request->headers->all(),
                'user_agent'  => $request->headers->get('User-Agent'),
                'content_type'=> $request->headers->get('Content-Type'),
            ]);
        }
    }

}
