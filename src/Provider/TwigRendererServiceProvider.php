<?php

declare(strict_types=1);

namespace Chiron\Twig\Provider;

use Chiron\Bootload\ServiceProvider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;
use Chiron\Twig\TwigEngineFactory;
use Chiron\Twig\TwigRenderer;
use Chiron\Views\TemplateRendererInterface;
use Twig\Environment;

final class TwigRendererServiceProvider implements ServiceProviderInterface
{
    public function register(BindingInterface $container): void
    {
        $container->singleton(Environment::class, new TwigEngineFactory());
        $container->singleton(TemplateRendererInterface::class, TwigRenderer::class);
    }
}
