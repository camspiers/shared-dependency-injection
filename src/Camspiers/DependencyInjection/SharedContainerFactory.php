<?php

namespace Camspiers\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use RuntimeException;

class SharedContainerFactory
{
    protected static $extensions = array();
    protected static $compilerPasses = array();

    public static function requireExtensionConfigs($path, $pattern = '*', $name = '_extensions.php')
    {
        $path = realpath($path);
        if (file_exists($path)) {
            foreach (glob("$path/$pattern/$name") as $file) {
                require_once $file;
            }
        }
    }

    public static function hasExtension($alias)
    {
        return isset(self::$extensions[$alias]);
    }

    public static function addExtension(ExtensionInterface $extension)
    {
        $alias = $extension->getAlias();
        if (!self::hasExtension($alias)) {
            self::$extensions[$alias] = $extension;
        } else {
            throw new RuntimeException("Extension with alias '$alias' has already been added");
            
        }
    }

    public static function replaceExtension(ExtensionInterface $extension)
    {
        self::$extensions[$alias] = $extension;
    }

    public static function removeExtension($alias)
    {
        if (self::hasExtension($alias)) {
            unset(self::$extensions[$alias]);
        }
    }

    public static function clearExtensions()
    {
        self::$extensions = array();
    }

    public static function hasCompilerPass($name)
    {
        return isset(self::$compilerPasses[$name]);
    }

    public static function addCompilerPass(CompilerPassInterface $compilerPass)
    {
        $name = get_class($compilerPass);
        if (!self::hasCompilerPass($name)) {
            self::$compilerPasses[$name] = $compilerPass;
        } else {
            throw new RuntimeException("Compiler pass of classname '$name' has already been added");
        }
    }

    public static function replaceCompilerPass(CompilerPassInterface $compilerPass)
    {
        self::$compilerPasses[get_class($compilerPass)] = $compilerPass;
    }

    public static function removeCompilerPass($name)
    {
        if (self::hasCompilerPass($name)) {
            unset(self::$compilerPasses[$name]);
        }
    }

    public static function clearCompilerPasses()
    {
        self::$compilerPasses = array();
    }

    public static function createContainer(
        array $parameters = array(),
        $servicesLocation = false
    ) {
        $container = new ContainerBuilder();

        foreach (self::$extensions as $extension) {
            $container->registerExtension($extension);
        }

        foreach (self::$compilerPasses as $compilerPass) {
            $container->addCompilerPass($compilerPass);
        }

        if ($servicesLocation && file_exists($servicesLocation)) {
            $loader = new YamlFileLoader(
                $container,
                new FileLocator(dirname($servicesLocation))
            );
            $loader->load(basename($servicesLocation));
        }

        $container->getParameterBag()->add($parameters);

        return $container;
    }

    public static function dumpContainer(
        Container $container,
        $class,
        $location
    ) {
        if (!file_exists($location)) {
            throw new RuntimeException('Dump location does not exist');
        }

        $container->compile();

        $dumper = new PhpDumper($container);

        file_put_contents(
            realpath($location) . "/$class.php",
            $dumper->dump(
                array(
                    'class' => $class
                )
            )
        );
    }
}
