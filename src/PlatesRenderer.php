<?php

declare(strict_types=1);

namespace Chiron\Views;

use League\Plates\Engine;

class PlatesRenderer implements TemplateRendererInterface
{
    use AttributesTrait;

    /**
     * @var Engine
     */
    private $engine;

    public function __construct(Engine $engine = null)
    {
        $this->engine = $engine ?: $this->createPlatesEngine();
    }

    /**
     * Create a default Plates engine.
     */
    private function createPlatesEngine(): Engine
    {
        return new Engine();
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $name, array $params = []): string
    {
        //$params = $this->normalizeParams($params);
        $params = array_merge($this->attributes, $params);

        return $this->engine->render($name, $params);
    }

    /**
     * Add a path for template.
     *
     * Multiple calls to this method without a namespace will trigger an
     * E_USER_WARNING and act as a no-op. Plates does not handle non-namespaced
     * folders, only the default directory; overwriting the default directory
     * is likely unintended.
     */
    public function addPath(string $path, string $namespace = null): void
    {
        if (! $namespace && ! $this->engine->getDirectory()) {
            $this->engine->setDirectory($path);

            return;
        }
        if (! $namespace) {
            trigger_error('Cannot add duplicate un-namespaced path in Plates template adapter', E_USER_WARNING);

            return;
        }
        $this->engine->addFolder($namespace, $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        $paths = $this->engine->getDirectory()
            ? [$this->getDefaultPath()]
            : [];
        foreach ($this->getPlatesFolders() as $folder) {
            $paths[] = new TemplatePath($folder->getPath(), $folder->getName());
        }

        return $paths;
    }

    /**
     * Checks if the view exists.
     *
     * @param string $name Full template path or part of a template path
     *
     * @return bool True if the path exists
     */
    public function exists(string $name): bool
    {
        return $this->engine->exists($name);
    }

    /**
     * Create and return a TemplatePath representing the default Plates directory.
     */
    private function getDefaultPath(): TemplatePath
    {
        return new TemplatePath($this->engine->getDirectory());
    }

    /**
     * Return the internal array of plates folders.
     *
     * @return \League\Plates\Template\Folder[]
     */
    private function getPlatesFolders(): array
    {
        $folders = $this->engine->getFolders();
        $r = new \ReflectionProperty($folders, 'folders');
        $r->setAccessible(true);

        return $r->getValue($folders);
    }

    /**
     * Sets file extension for template loader.
     *
     * @param string $extension Template files extension
     *
     * @return $this
     */
    // TODO : méthode à virer ???? et donc forcer dans le constructeur d'avoir un objet Engine déjà initialisé avec les bonnes extensions ????
    public function setFileExtension(string $extension): self
    {
        $this->engine->setFileExtension($extension);

        return $this;
    }
}
