<?php

namespace Camspiers\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;

class SharedContainerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SharedContainerFactory::clearExtensions();
        SharedContainerFactory::clearCompilerPasses();
    }

    public function testAddExtension()
    {
        SharedContainerFactory::addExtension(new DummyExtension);
        $this->assertTrue(SharedContainerFactory::hasExtension('dummy'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Extension with alias 'dummy' has already been added
     */
    public function testAddExtensionTwice()
    {
        SharedContainerFactory::addExtension(new DummyExtension);
        SharedContainerFactory::addExtension(new DummyExtension);
    }

    public function testRemoveExtension()
    {
        SharedContainerFactory::addExtension(new DummyExtension);
        SharedContainerFactory::removeExtension('dummy');
        $this->assertFalse(SharedContainerFactory::hasExtension('dummy'));
    }

    public function testClearExtensions()
    {
        SharedContainerFactory::addExtension(new DummyExtension);
        SharedContainerFactory::clearExtensions();
        $this->assertFalse(SharedContainerFactory::hasExtension('dummy'));
    }

    public function testAddCompilerPass()
    {
        SharedContainerFactory::addCompilerPass($cp = new DummyCompilerPass);
        $this->assertTrue(SharedContainerFactory::hasCompilerPass(get_class($cp)));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Compiler pass of classname 'Camspiers\DependencyInjection\DummyCompilerPass' has already been added
     */
    public function testAddCompilerPassTwice()
    {
        SharedContainerFactory::addCompilerPass(new DummyCompilerPass);
        SharedContainerFactory::addCompilerPass(new DummyCompilerPass);
    }

    public function testRemoveCompilerPass()
    {
        SharedContainerFactory::addCompilerPass($cp = new DummyCompilerPass);
        SharedContainerFactory::removeCompilerPass(get_class($cp));
        $this->assertFalse(SharedContainerFactory::hasCompilerPass(get_class($cp)));
    }

    public function testClearCompilerPass()
    {
        SharedContainerFactory::addCompilerPass($cp = new DummyCompilerPass);
        SharedContainerFactory::clearCompilerPasses();
        $this->assertFalse(SharedContainerFactory::hasExtension(get_class($cp)));
    }

    public function testCreateContainer()
    {
        $this->assertTrue(SharedContainerFactory::createContainer() instanceof Container);
    }

    public function testCreateContainerWithExtension()
    {
        $extension = new DummyExtension();
        SharedContainerFactory::addExtension($extension);
        $container = SharedContainerFactory::createContainer();
        $this->assertTrue($container instanceof Container);
        $this->assertEquals($extension, $container->getExtension('dummy'));
    }
    
    public function testCreateContainerWithCompilerPass()
    {
        SharedContainerFactory::addExtension(new DummyExtension());
        SharedContainerFactory::addCompilerPass(new DummyCompilerPass());
        $container = SharedContainerFactory::createContainer();
        $this->assertTrue($container instanceof Container);
        $container->loadFromExtension('dummy');
        ob_start();
        $container->compile();
        $this->assertEquals('test', ob_get_clean());
    }
}
