<?php

namespace App\EventSubscriber;

use App\Repository\ConferenceRepository;
use Twig\Environment;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $repo;

    public function __construct(Environment $twig, ConferenceRepository $repo)
    {
        $this->twig = $twig;
        $this->repo = $repo;
    }
    public function onControllerEvent(ControllerEvent $event)
    {
        // ...
        $this->twig->addGlobal('conferences', $this->repo->findAll());
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.controller' => 'onControllerEvent',
        ];
    }
}
