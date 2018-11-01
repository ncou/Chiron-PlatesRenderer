<?php

declare(strict_types=1);

namespace Chiron\Views\Tests;

use Chiron\Container\Container;
use Chiron\Views\Provider\PlatesRendererServiceProvider;
use Chiron\Views\TemplateRendererInterface;
use Chiron\Views\PlatesRenderer;
use PHPUnit\Framework\TestCase;

class PlatesRendererServiceProviderTest extends TestCase
{
    public function testWithoutTemplatesSettingsInTheContainer()
    {
        $c = new Container();
        (new PlatesRendererServiceProvider())->register($c);

        $renderer = $c->get(PlatesRenderer::class);
        $this->assertInstanceOf(PlatesRenderer::class, $renderer);
        $this->assertEmpty($renderer->getPaths());

        $this->assertEquals($renderer->getExtension(), 'html');

        // test the instance using the container alias
        $alias = $c->get(TemplateRendererInterface::class);
        $this->assertInstanceOf(PlatesRenderer::class, $alias);
    }

    public function testWithTemplatesSettingsInTheContainer()
    {
        $c = new Container();
        $c['templates'] = ['extension' => 'plates.php', 'paths'     => ['foobar' => '/', 'tests/']];
        (new PlatesRendererServiceProvider())->register($c);

        $renderer = $c->get(PlatesRenderer::class);
        $this->assertInstanceOf(PlatesRenderer::class, $renderer);
        $this->assertNotEmpty($renderer->getPaths());

        $this->assertEquals($renderer->getExtension(), 'plates.php');

        $paths = $renderer->getPaths();

        $this->assertEquals($paths[0]->getNamespace(), 'foobar');
        $this->assertEquals($paths[0]->getPath(), '/');

        $this->assertNull($paths[1]->getNamespace());
        $this->assertEquals($paths[1]->getPath(), 'tests/');
    }
}
