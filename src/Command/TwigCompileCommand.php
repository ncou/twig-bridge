<?php

declare(strict_types=1);

namespace Chiron\Twig\Command;

use Chiron\Core\Command\AbstractCommand;
use Chiron\Filesystem\Filesystem;
use Chiron\Twig\TwigRenderer;
use Chiron\Views\TemplateRendererInterface;
use LogicException;
use Twig\Error\Error as TwigErrorException;
use Twig\Loader\FilesystemLoader;
use Twig\Source;

//https://github.com/symfony/twig-bridge/blob/17cbe5aa0a503c67d76dd6248ab8a3a856cf7105/Command/LintCommand.php
//https://github.com/symfony/symfony/blob/9a4a96910d02275cc3a7912def65a6e39fec542d/src/Symfony/Bridge/Twig/Command/LintCommand.php

//https://github.com/narrowspark/framework/blob/3d39c891d93c0bc5b7f0148421abbf7143cd1813/src/Viserio/Bridge/Twig/Command/LintCommand.php
//https://github.com/narrowspark/framework/blob/81f39d7371715ee20aa888a8934c36c536e3d69e/src/Viserio/Provider/Twig/Command/LintCommand.php

//https://github.com/narrowspark/framework/blob/2a3536b821e685a3c7aa09f9a9b6eec9d873004f/src/Viserio/Bridge/Twig/Tests/Command/LintCommandTest.php
//https://github.com/narrowspark/framework/blob/81f39d7371715ee20aa888a8934c36c536e3d69e/src/Viserio/Provider/Twig/Tests/Command/LintCommandTest.php

/**
 * Console command to check the syntax of Twig templates.
 *
 * Adapted from the Symfony TwigBundle:
 *
 * @see https://github.com/symfony/TwigBundle/blob/master/Command/LintCommand.php
 */
final class TwigCompileCommand extends AbstractCommand
{
    /** @var \Twig\Environment */
    private $twig;

    protected static $defaultName = 'twig:compile';

    protected function configure(): void
    {
        $this->setDescription('Check compilation errors in the Twig templates files.');
    }

    public function perform(Filesystem $filesystem, TemplateRendererInterface $renderer): int
    {
        $loader = $this->getLoader($renderer);
        $extension = '*.' . $renderer->getExtension();

        $details = [];
        foreach ($loader->getNamespaces() as $namespace) {
            foreach ($loader->getPaths($namespace) as $path) {
                foreach ($filesystem->find($path, $extension) as $file) {
                    $template = '@' . $namespace . '/' . $file->getBasename();
                    $source = $loader->getSourceContext($template);

                    $details[] = $this->validate($source);
                }
            }
        }

        return $this->display($details);
    }

    /**
     * Assert the twig render is correct and return the twig file loader.
     *
     * @param TemplateRendererInterface $renderer
     *
     * @return FilesystemLoader
     */
    private function getLoader(TemplateRendererInterface $renderer): FilesystemLoader
    {
        if (! $renderer instanceof TwigRenderer) {
            throw new LogicException(
                sprintf(
                    'The renderer object must be a "%s" instance.',
                    TwigRenderer::class
                )
            );
        }

        $this->twig = $renderer->twig();
        $loader = $this->twig->getLoader();

        if (! $loader instanceof FilesystemLoader) {
            throw new LogicException(
                sprintf(
                    'The loader object defined in the TwigRenderer must be a "%s" instance.',
                    FilesystemLoader::class
                )
            );
        }

        return $loader;
    }

    /**
     * Validate the twig template source.
     *
     * @param Source $source Twig template source
     *
     * @return array
     */
    // TODO : renommer la méthode en validateCompilation() ou validateSource() ou un truc du genre ????
    private function validate(Source $source): array
    {
        try {
            $this->twig->compileSource($source);
        } catch (TwigErrorException $exception) {
            return [
                'template' => $source->getCode(),
                'file'     => $source->getPath(),
                'valid'    => false,
                'error'    => $exception,
            ];
        }

        return [
            'template' => $source->getCode(),
            'file'     => $source->getPath(),
            'valid'    => true,
        ];
    }

    /**
     * Output the results as text.
     *
     * @param array $details validation results from all linted files
     *
     * @return integer
     */
    private function display(array $details): int
    {
        $verbose = $this->isVerbose();
        $errors = 0;

        foreach ($details as $detail) {
            if ($detail['valid'] && $verbose) {
                $this->info('OK in ' . $detail['file']);
            } elseif (! $detail['valid']) {
                $errors++;
                $this->renderTwigError($detail);
            }
        }

        $countDetails = count($details);

        if ($countDetails === 0) {
            $this->warning('No twig files found in the loader paths.');
        } elseif ($errors === 0) {
            $this->success(sprintf('All %d Twig files contain valid syntax.', $countDetails));
        } else {
            $this->warning(sprintf('%d Twig files have valid syntax and %d contain errors.', $countDetails - $errors, $errors));
        }

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Output the error to the console.
     *
     * @param array $detail Informations for the file that failed to be compiled
     *
     * @return void
     */
    private function renderTwigError(array $detail): void
    {
        $error = $detail['error'];
        $line = $error->getTemplateLine();

        // It's a generic syntax error so there is no line associated.
        if ($line === -1) {
            $this->line(sprintf('<error> SYNTAX ERROR </error> in %s (%s)', $detail['file'], $error->getRawMessage()));
        } else {
            $this->line(sprintf('<error> ERROR </error> in %s (line %s)', $detail['file'], $line));

            $lines = $this->getContext($detail['template'], $line);

            foreach ($lines as $lineNumber => $code) {
                $this->line(sprintf(
                    '%s %-6s %s',
                    $lineNumber === $line ? '<error> >> </error>' : '    ',
                    $lineNumber,
                    $code
                ));

                if ($lineNumber === $line) {
                    $this->line(sprintf('<error> >> %s</error> ', $error->getRawMessage()));
                }
            }
        }
    }

    /**
     * Grabs the surrounding lines around the exception.
     *
     * @param string $template contents of Twig template
     * @param integer $line line where the exception occurred
     * @param integer $context number of lines around the line where the exception occurred
     *
     * @return array
     */
    private function getContext(string $template, int $line, int $context = 3): array
    {
        $lines = explode("\n", $template);
        $position = max(0, $line - $context);
        $max = min(count($lines), $line - 1 + $context);

        $result = [];
        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            $position++;
        }

        return $result;
    }
}
