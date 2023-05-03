<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => match ($exception->getStatusCode()){
                    404 => "L'élément recherché n'existe pas.",
                    403 => "Vous n'avez pas les droits suffisants pour effectuer cette action.",
                    default => "Le serveur n'a pas pu répondre à votre demande."
                }
            ];
            $event->setResponse(new JsonResponse($data));
    }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
