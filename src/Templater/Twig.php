<?php
declare(strict_types=1);

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
     * @throws Exception\InvalidArgument
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        try {
            $loader = new \Twig\Loader\FilesystemLoader($this->options['dir']);

            $this->twig = new \Twig\Environment($loader, [
                'debug' => $this->options['debug'] ?? false,

                'cache' => $this->options['cache'] ?? false,

                'auto_reload' => $this->options['reload'] ?? false,

                'strict_variables' => $this->options['strict'] ?? false,

                'autoescape' => 'name',
            ]);
        } catch (\LogicException $e) {
            throw (new Exception\Logic($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        } catch (\Throwable $e) {
            throw (new Exception\Runtime($e->getMessage()))
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
                $this->twig->addFunction(new \Twig\TwigFunction($name, $value));
            }
        }
    }

    /**
     * Transforms template to page.
     *
     * If context is an object, then only public non-static properties will be taken.
     *
     * @throws Exception\Logic
     * @throws Exception\Runtime
     */
    public function transform(string $filename, array|object|null $context = null): string
    {
        $timer = gettimeofday(true);

        $filename = $this->normalizeFilename($filename, 'twig');

        $context = $this->normalizeContext($context);

        try {
            $contents = $this->twig->render($filename, $context);
        } catch (\LogicException $e) {
            throw (new Exception\Logic($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        } catch (\Throwable $e) {
            throw (new Exception\Runtime($e->getMessage()))
                ->setFile($e->getFile())
                ->setLine($e->getLine());
        }

        self::$timer += gettimeofday(true) - $timer;

        self::$counter += 1;

        return $contents;
    }
}
