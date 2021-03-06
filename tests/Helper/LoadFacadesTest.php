<?php

declare(strict_types=1);

namespace Chiron\Twig\Tests\Helper;

use Chiron\Core\Configure;
use Chiron\Core\Directories;
use Chiron\Container\Container;
use Chiron\Twig\Config\TwigConfig;
use Chiron\Twig\Tests\Helper\Fixtures\Html;
use Chiron\Twig\TwigEngineFactory;
use Chiron\Twig\TwigRenderer;
use PHPUnit\Framework\TestCase;

class LoadFacadesTest extends TestCase
{
    private $container;

    /**
     * @expectedException \Chiron\Config\Exception\ConfigException
     *
     * @expectedExceptionMessage [Failed assertion "facades array structure" for option 'facades' with value array.]
     */
    public function testCallStaticClassProxyWithoutValidConfigMissingClassKey()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => ['missing_class_key' => Html::class],
            ],
        ]);
    }

    /**
     * @expectedException \Chiron\Config\Exception\ConfigException
     *
     * @expectedExceptionMessage [Failed assertion "facades array structure" for option 'facades' with value array.]
     */
    public function testCallStaticClassProxyWithoutValidConfigBadKey()
    {
        $config = $this->prepareConfig([
            'facades' => [
                Html::class,
            ],
        ]);
    }

    /**
     * @expectedException \Chiron\Config\Exception\ConfigException
     *
     * @expectedExceptionMessage [Failed assertion "facades array structure" for option 'facades' with value array.]
     */
    public function testCallStaticClassProxyWithoutValidConfigBadValue()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => Html::class,
            ],
        ]);
    }

    /**
     * @expectedException \Twig\Error\RuntimeError
     *
     * @expectedExceptionMessage The method "Non_Existing_Class::helloWorld" does not exist.
     */
    public function testThrowExceptionWhenMethodDoesntExistAndStrictModeIsOn()
    {
        $config = $this->prepareConfig([
            'options' => ['strict_variables' => true],
            'facades' => [
                'Html' => ['class' => 'Non_Existing_Class'],
            ],
        ]);

        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('', $result);
    }

    public function testReturnEmptyStringWhenMethodDoesntExistAndStrictModeIsOff()
    {
        $config = $this->prepareConfig([
            'options' => ['strict_variables' => false],
            'facades' => [
                'Html' => ['class' => 'Non_Existing_Class'],
            ],
        ]);

        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('', $result);
    }

    public function testCallStaticClassProxyWithoutSettings()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => ['class' => Html::class],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('&lt;strong&gt;Hello world&lt;/strong&gt;', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtFalse()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => false,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('&lt;strong&gt;Hello world&lt;/strong&gt;', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtTrue()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => true,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('<strong>Hello world</strong>', $result);
    }

    // ensure the twig "|raw" function is not disturbed when using the is_safe flag !
    public function testCallStaticClassProxyWithOptionIsSafeAtFalseAndUsingRawFunction()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => false,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('raw.html.twig');

        $this->assertEquals('<strong>Hello world</strong>', $result);
    }

    // ensure the twig "|raw" function is not disturbed when using the is_safe flag !
    public function testCallStaticClassProxyWithOptionIsSafeAtTrueAndUsingRawFunction()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => true,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('raw.html.twig');

        $this->assertEquals('<strong>Hello world</strong>', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtTrueForMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => ['helloWorld'],
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('<strong>Hello world</strong>', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtTrueForStringableMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => ['helloWorldStringable'],
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello_stringable.html.twig');

        $this->assertEquals('<strong>Hello world</strong>', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtFalseForStringableMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => false,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello_stringable.html.twig');

        $this->assertEquals('&lt;strong&gt;Hello world&lt;/strong&gt;', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtTrueForNonStringableMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => true,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello_non_stringable.html.twig');

        $this->assertEquals('1234.5', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtFalseForNonStringableMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => false,
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello_non_stringable.html.twig');

        $this->assertEquals('1234.5', $result);
    }

    public function testCallStaticClassProxyWithOptionIsSafeAtTrueForNonExistingMethod()
    {
        $config = $this->prepareConfig([
            'facades' => [
                'Html' => [
                    'class'   => Html::class,
                    'is_safe' => ['non_existing_method'],
                ],
            ],
        ]);
        $factory = new TwigEngineFactory($config);
        $twigEnvironment = $factory($config, $this->container);

        $renderer = new TwigRenderer($twigEnvironment);
        $renderer->addPath(__DIR__ . '/Fixtures');
        $result = $renderer->render('hello.html.twig');

        $this->assertEquals('&lt;strong&gt;Hello world&lt;/strong&gt;', $result);
    }

    private function prepareConfig(array $settings): TwigConfig
    {
        $this->container = $this->initContainer();

        return new TwigConfig($settings);
    }

    private function initContainer(): Container
    {
        $container = new Container();

        // TODO : il faudra surement initialiser la matuation sur les classes de config plutot que de faire un merge !!!!
        $configure = $container->get(Configure::class);
        $configure->merge('settings', ['debug' => true, 'charset' => 'UTF-8', 'timezone' => 'UTC']);

        $directories = $container->get(Directories::class);
        $directories->set('@cache', sys_get_temp_dir());

        return $container;
    }
}
