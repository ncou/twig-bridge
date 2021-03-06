<?php

declare(strict_types=1);

namespace Chiron\Views\Tests\Fixtures;

class StaticAndConsts
{
    public const FIRST_CONST = 'I am a const!';

    public static $staticVar = 'I am a static var!';

    public static function sticFunction($var)
    {
        return "I am a static function with param ${var}!";
    }
}
