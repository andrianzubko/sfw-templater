<?php

namespace SFW\Templater;

/**
 * Twig templater.
 */
class Twig extends Processor
{
    /**
     * Twig instance.
     */
    protected \Twig\Environment $twig;

    /**
     * Passes parameters to properties and checking some.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->options['minify'] ??= true;

        $this->options['debug'] ??= false;

        try {
            $loader = new \Twig\Loader\FilesystemLoader($this->options['dir']);

            $this->twig = new \Twig\Environment($loader, [
                'debug' => $this->options['debug'],

                'cache' => $this->options['cache'] ?? false,

                'auto_reload' => $this->options['reload'] ?? false,

                'strict_variables' => $this->options['strict'] ?? false,

                'autoescape' => 'name',
            ]);
        } catch (\LogicException $e) {
            throw (new LogicException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        } catch (\Throwable $e) {
            throw (new RuntimeException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        if (isset($this->options['globals'])) {
            foreach ($this->options['globals'] as $name => $value) {
                $this->twig->addGlobal($name, $value);
            }
        }

        if (isset($this->options['functions'])) {
            foreach ($this->options['functions'] as $name => $value) {
                $this->twig->addFunction(
                    new \Twig\TwigFunction($name, $value)
                );
            }
        }
    }

    /**
     * Transforms template to page.
     *
     * @throws LogicException
     */
    public function transform(string $filename, array|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->normalizeFilename($filename, 'twig');

        try {
            $contents = $this->twig->render($filename, (array) $context);
        } catch (\LogicException $e) {
            throw (new LogicException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        } catch (\Throwable $e) {
            throw (new RuntimeException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        if ($this->options['minify']
            && $this->mime === 'text/html'
        ) {
            $contents = $this->options['debug']
                ? Util\HTMLDebugger::transform($contents)
                : Util\HTMLMinifier::transform($contents);
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
