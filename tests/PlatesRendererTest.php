<?php

use Chiron\Views\PlatesRenderer;
use Chiron\Views\TemplatePath;
use League\Plates\Engine;
use PHPUnit\Framework\TestCase;

class PlatesRendererTest extends TestCase
{
    /**
     * @var Engine
     */
    private $platesEngine;

    /**
     * @var bool
     */
    private $error;

    protected function setUp()
    {
        $this->error = false;
        $this->platesEngine = new Engine();
    }

    public function assertTemplatePath($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath contained path %s', $path);
        $this->assertEquals($path, $templatePath->getPath(), $message);
    }

    public function assertTemplatePathString($path, TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: sprintf('Failed to assert TemplatePath casts to string path %s', $path);
        $this->assertEquals($path, (string) $templatePath, $message);
    }

    public function assertTemplatePathNamespace($namespace, TemplatePath $templatePath, $message = null)
    {
        $message = $message
            ?: sprintf('Failed to assert TemplatePath namespace matched %s', var_export($namespace, true));
        $this->assertEquals($namespace, $templatePath->getNamespace(), $message);
    }

    public function assertEmptyTemplatePathNamespace(TemplatePath $templatePath, $message = null)
    {
        $message = $message ?: 'Failed to assert TemplatePath namespace was empty';
        $this->assertEmpty($templatePath->getNamespace(), $message);
    }

    public function assertEqualTemplatePath(TemplatePath $expected, TemplatePath $received, $message = null)
    {
        $message = $message ?: 'Failed to assert TemplatePaths are equal';
        if ($expected->getPath() !== $received->getPath()
            || $expected->getNamespace() !== $received->getNamespace()
        ) {
            $this->fail($message);
        }
    }

    public function testCanProvideEngineAtInstantiation()
    {
        $renderer = new PlatesRenderer($this->platesEngine);
        $this->assertInstanceOf(PlatesRenderer::class, $renderer);
        $this->assertEmpty($renderer->getPaths());
    }

    public function testLazyLoadsEngineAtInstantiationIfNoneProvided()
    {
        $renderer = new PlatesRenderer();
        $this->assertInstanceOf(PlatesRenderer::class, $renderer);
        $this->assertEmpty($renderer->getPaths());
    }

    public function testCanAddPath()
    {
        $renderer = new PlatesRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');
        $paths = $renderer->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertEmptyTemplatePathNamespace($paths[0]);

        return $renderer;
    }

    /**
     * @param PlatesRenderer $renderer
     * @depends testCanAddPath
     */
    public function testAddingSecondPathWithoutNamespaceIsANoopAndRaisesWarning($renderer)
    {
        $paths = $renderer->getPaths();
        $path = array_shift($paths);
        set_error_handler(function ($error, $message) {
            $this->error = true;
            $this->assertContains('duplicate', $message);

            return true;
        }, E_USER_WARNING);
        $renderer->addPath(__DIR__);
        restore_error_handler();
        $this->assertTrue($this->error, 'Error handler was not triggered when calling addPath() multiple times');
        $paths = $renderer->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $test = array_shift($paths);
        $this->assertEqualTemplatePath($path, $test);
    }

    public function testCanAddPathWithNamespace()
    {
        $renderer = new PlatesRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset', 'test');
        $paths = $renderer->getPaths();
        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertTemplatePath(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathString(__DIR__ . '/TestAsset', $paths[0]);
        $this->assertTemplatePathNamespace('test', $paths[0]);
    }

    public function testDelegatesRenderingToUnderlyingImplementation()
    {
        $renderer = new PlatesRenderer();
        $renderer->addPath(__DIR__ . '/TestAsset');
        $result = $renderer->render('testTemplate', ['hello' => 'Hi']);
        $this->assertEquals('Hi', $result);
    }
}
