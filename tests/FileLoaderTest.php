<?php
/**
 * This file is part of the Madapaja.TwigModule package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Madapaja\TwigModule\Resource\Page;

use BEAR\Resource\Code;
use Madapaja\TwigModule\TwigAppMetaTestModule;
use Madapaja\TwigModule\TwigFileLoaderTestModule;
use Madapaja\TwigModule\TwigModule;
use Madapaja\TwigModule\TwigRenderer;
use PHPUnit_Framework_TestCase;
use Ray\Di\Injector;

class FileLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    private $injector;

    public function getInjector() : Injector
    {
        return new Injector(new TwigFileLoaderTestModule([$_ENV['TEST_DIR'] . '/Fake/src/Resource']));
    }

    public function testRenderer()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(Index::class);
        $prop = (new \ReflectionClass($ro))->getProperty('renderer');
        $prop->setAccessible(true);

        $this->assertInstanceOf(TwigRenderer::class, $prop->getValue($ro));
    }

    public function testTwigOptions()
    {
        /** @var $renderer TwigRenderer */
        $renderer = (new Injector(new TwigFileLoaderTestModule([$_ENV['TEST_DIR'] . '/Fake/src/Resource'], ['debug' => true])))->getInstance(TwigRenderer::class);
        $this->assertTrue($renderer->twig->isDebug());

        /** @var $renderer TwigRenderer */
        $renderer = (new Injector(new TwigFileLoaderTestModule([$_ENV['TEST_DIR'] . '/Fake/src/Resource'], ['debug' => false])))->getInstance(TwigRenderer::class);
        $this->assertFalse($renderer->twig->isDebug());
    }

    public function testIndex()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(Index::class);

        $this->assertSame('Hello, BEAR.Sunday!', trim((string) $ro->onGet()));
    }

    public function testTemplatePath()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(Index::class);

        $this->assertSame('Your Name is MyName!', trim((string) $ro->onPost('MyName')));
    }

    public function testIndexWithArg()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(Index::class);

        $this->assertSame('Hello, Madapaja!', trim((string) $ro->onGet('Madapaja')));
    }

    /**
     * @expectedException \Madapaja\TwigModule\Exception\TemplateNotFound
     */
    public function testTemplateNotFoundException()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(NoTemplate::class);
        $prop = (new \ReflectionClass($ro))->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->getValue($ro)->render($ro);
    }

    public function testNoViewWhenCode301()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(NoTemplate::class);
        $ro->code = 303;
        $prop = (new \ReflectionClass($ro))->getProperty('renderer');
        $prop->setAccessible(true);
        $view = $prop->getValue($ro)->render($ro);
        $this->assertSame('', $view);
    }

    public function testNoContent()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(NoTemplate::class);
        $ro->code = Code::NO_CONTENT;
        $prop = (new \ReflectionClass($ro))->getProperty('renderer');
        $prop->setAccessible(true);
        $view = $prop->getValue($ro)->render($ro);
        $this->assertSame('', $view);
    }

    public function testPage()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(Page::class);

        $this->assertSame('<!DOCTYPE html><html><head><title>Page</title><body>Hello, BEAR.Sunday!</body></html>', (string) $ro->onGet());
    }

    public function testCode()
    {
        $injector = $this->getInjector();;
        $ro = $injector->getInstance(\Madapaja\TwigModule\Resource\Page\Code::class);

        $this->assertSame('code:200 date:Tue, 15 Nov 1994 12:45:26 GMT', (string) $ro->onGet());
    }

    public function testIndexTemplateWithoutPaths()
    {
        $injector = new Injector(new TwigAppMetaTestModule);
        $ro = $injector->getInstance(Index::class);
        $ro->onGet();
        $this->assertSame('Hello, BEAR.Sunday!', trim((string) $ro));
    }
}
