<?php

declare(strict_types=1);

namespace Chiron\Twig\Tests\Extension;

use Chiron\Container\Container;
use Chiron\Http\Psr\ServerRequest;
use Chiron\Http\Psr\Uri;
use Chiron\Http\RequestContext;
use Chiron\FastRoute\UrlGenerator;
use Chiron\Routing\RouteCollection;
use Chiron\Routing\Target\TargetFactory;
use Chiron\Routing\Route;
use Chiron\Twig\Extension\RoutingExtension;
use Chiron\Twig\TwigRenderer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class RoutingExtensionTest extends TestCase
{
    /**
     * @var Twig_Environment
     */
    private $twigEnvironment;

    protected function setUp()
    {
        $this->twigEnvironment = new Environment(new FilesystemLoader());

        $container = new Container();

        $targetFactory = new TargetFactory($container);
        $routes = new RouteCollection($targetFactory);
        $routes->addRoute(Route::any('/my/target/path/')->name('route_name'));
        $routes->addRoute(Route::any('/hello/{name}')->name('route_name_advanced'));

        $urlGenerator = new UrlGenerator($routes);

        $request = new ServerRequest('GET', new Uri('https://www.foo.bar/'));
        $container->bind(ServerRequestInterface::class, $request);
        $requestContext = new RequestContext($container);

        $this->twigEnvironment->addExtension(new RoutingExtension($urlGenerator, $requestContext));
    }

    public function testRoutingExtensionAbsoluteUrlForMethod()
    {
        $renderer = new TwigRenderer($this->twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('absoluteUrlFor.html.twig');

        $this->assertEquals('https://www.foo.bar/my/target/path/', $result);
    }

    public function testRoutingExtensionRelativeUrlForMethod()
    {
        $renderer = new TwigRenderer($this->twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('relativeUrlFor.html.twig');

        $this->assertEquals('/my/target/path/', $result);
    }

    public function testRoutingExtensionAbsoluteUrlForMethodAdvanced()
    {
        $renderer = new TwigRenderer($this->twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('absoluteUrlFor_advanced.html.twig');

        $this->assertEquals('https://www.foo.bar/hello/josh?query1=value1&query2=value2', $result);
    }

    public function testRoutingExtensionRelativeUrlForMethodAdvanced()
    {
        $renderer = new TwigRenderer($this->twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('relativeUrlFor_advanced.html.twig');

        $this->assertEquals('/hello/josh?query1=value1&query2=value2', $result);
    }
}
