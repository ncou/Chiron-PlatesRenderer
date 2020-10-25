<?php

namespace Chiron\Views\Provider;

use Chiron\Views\PlatesRenderer;
use Chiron\Views\TemplateRendererInterface;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Chiron\Core\Container\Provider\ServiceProviderInterface;
use Chiron\Container\BindingInterface;

class PlatesRendererServiceProvider implements ServiceProviderInterface
{
    /**
     * You should have in your container the config informations using the following structure :.
     *
     * 'templates' => [
     *     'extension' => 'file extension used by templates; defaults to html',
     *     'paths' => [
     *         // namespace / path pairs
     *         //
     *         // Numeric namespaces imply the default/main namespace. Paths may be
     *         // strings or arrays of string paths to associate with the namespace.
     *     ],
     * ],
     */
    public function register(BindingInterface $container): void
    {
        // add default config settings if not already presents in the container.
        if (! $container->has('templates')) {
            $container['templates'] = [
                'extension' => 'html',
                'paths'     => [],
            ];
        }

        // *** Factories ***
        $container[PlatesRenderer::class] = function ($c) {
            // TODO : créer une classe EngineFactory avec la gestion des fonctions et des extensions. cf twigrenderer
            $renderer = new PlatesRenderer(new Engine());
            // grab the config settings in the container.
            $config = $c->get('templates');
            // Add template file extension.
            $renderer->setExtension($config['extension']);
            // Add template paths.
            $allPaths = isset($config['paths']) && is_array($config['paths']) ? $config['paths'] : [];
            foreach ($allPaths as $namespace => $paths) {
                $namespace = is_numeric($namespace) ? null : $namespace;
                foreach ((array) $paths as $path) {
                    $renderer->addPath($path, $namespace);
                }
            }

            return $renderer;
        };

        // *** Alias ***
        //$container->alias(TemplateRendererInterface::class, PlatesRenderer::class);
    }
}
