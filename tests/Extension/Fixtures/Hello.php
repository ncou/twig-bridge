<?php

declare(strict_types=1);

namespace Chiron\Twig\Tests\Extension\Fixtures;

class Hello
{
    public function helloWorld(): string
    {
        return 'Hello world';
    }
}
