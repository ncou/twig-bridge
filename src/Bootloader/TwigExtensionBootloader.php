<?php

declare(strict_types=1);

namespace Chiron\Twig\Bootloader;

use Chiron\Core\Container\Bootloader\AbstractBootloader;
use Chiron\Injector\FactoryInterface;
use Chiron\RequestContext\RequestContext;
use Chiron\Router\UrlGeneratorInterface;
use Chiron\Twig\Extension\ContainerExtension;
use Chiron\Twig\Extension\RoutingExtension;
use Twig\Environment;
use Twig\Extension\DebugExtension;

final class TwigExtensionBootloader extends AbstractBootloader
{
    public function boot(Environment $twig, FactoryInterface $factory): void
    {
        $twig->addExtension($factory->build(ContainerExtension::class));

        if (setting('debug') === true) {
            // Twig Debug extension provide access to the "dump()" function.
            $twig->addExtension($factory->build(DebugExtension::class));
        }

        // if the "http" and "router" classes are presents we enable the extension.
        if (di()->has(UrlGeneratorInterface::class) && di()->has(RequestContext::class)) {
            $twig->addExtension($factory->build(RoutingExtension::class));
        }
    }
}
