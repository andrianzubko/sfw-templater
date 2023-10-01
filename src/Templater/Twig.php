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

        try {
            $loader = new \Twig\Loader\FilesystemLoader($this->options['dir']);
        } catch (\Twig\Error\LoaderError $e) {
            throw (new LogicException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        $this->twig = new \Twig\Environment($loader,
            [
                'debug' => $this->options['debug'] ?? false,

                'cache' => $this->options['cache'] ?? false,

                'auto_reload' => $this->options['reload'] ?? true,

                'strict_variables' => $this->options['strict'] ?? true,

                'autoescape' => $this->options['autoescape'] ?? 'html',
            ]
        );

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
    public function transform(string $template, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        try {
            $contents = $this->twig->render($template, (array) $context);
        } catch (
            \Twig\Error\LoaderError | \Twig\Error\SyntaxError $e
        ) {
            throw (new LogicException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        } catch (
            \Twig\Error\RuntimeError $e
        ) {
            throw (new RuntimeException($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
