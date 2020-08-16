<?php

declare(strict_types=1);

namespace Chiron\Twig\Bootloader;

use Chiron\Bootload\AbstractBootloader;
use Chiron\Console\Console;
use Chiron\Twig\Command\TwigClearCommand;
use Chiron\Twig\Command\TwigCompileCommand;
use Chiron\Twig\Command\TwigDebugCommand;
use Chiron\Twig\Command\TwigVersionCommand;

final class TwigCommandBootloader extends AbstractBootloader
{
    public function boot(Console $console): void
    {
        $console->addCommand(TwigClearCommand::getDefaultName(), TwigClearCommand::class);
        $console->addCommand(TwigCompileCommand::getDefaultName(), TwigCompileCommand::class);
        $console->addCommand(TwigDebugCommand::getDefaultName(), TwigDebugCommand::class);
        $console->addCommand(TwigVersionCommand::getDefaultName(), TwigVersionCommand::class);
    }
}
