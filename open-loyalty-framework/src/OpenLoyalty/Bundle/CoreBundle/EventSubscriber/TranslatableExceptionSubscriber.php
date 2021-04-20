<?php
/**
 * Copyright © 2017 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\CoreBundle\EventSubscriber;

use OpenLoyalty\Component\Core\Domain\Exception\Translatable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TranslatableExceptionSubscriber.
 */
class TranslatableExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * TranslatableExceptionSubscriber constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['translateException', 200],
            ],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function translateException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (!$exception instanceof Translatable) {
            return;
        }

        $message = $this->translator->trans($exception->getMessageKey(), $exception->getMessageParams());
        $event->setResponse(new JsonResponse($message, Response::HTTP_BAD_REQUEST));
    }
}
