<?php

namespace Bolt\Extension\Bolt\EmailSpooler;

use Bolt\Extension\SimpleExtension;
use Silex\Application;
use Swift_FileSpool as SwiftFileSpool;
use Swift_Mailer as SwiftMailer;
use Swift_Transport_SpoolTransport as SwiftTransportSpoolTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Email spooler extension loader.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class EmailSpoolerExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['mailer'] = $app->share(
            function () use ($app) {
                $app['mailer.initialized'] = true;
                $spoolDir = $app['resources']->getPath('cache/.spool');
                $transport = new SwiftTransportSpoolTransport($app['swiftmailer.transport.eventdispatcher'], new SwiftFileSpool($spoolDir));

                return new SwiftMailer($transport);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        /** @var Application $app */
        $app = $this->getContainer();
        $dispatcher->addListener(KernelEvents::TERMINATE, [new EventListener\QueueListener($app), 'flush']);
    }
}