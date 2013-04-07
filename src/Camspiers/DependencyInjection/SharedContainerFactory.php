<?php

namespace Camspiers\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class SharedContainerFactory
 * @package Camspiers\DependencyInjection
 */
class SharedContainerFactory
{
    /**
     * @var array
     */
    protected static $extensions = array();
    /**
     * @var array
     */
    protected static $compilerPasses = array();
    /**
     * Require a bunch of files specified by path patterns
     *
     * E.g.
     * SharedContainerFactory::requireExtensionConfigs(
     *     array(
     *         __DIR__ . '/*\/_extensions.php'
     *     )
     * );
     * @param array $patterns
     */
    public static function requireExtensionConfigs(array $patterns = array())
    {
        foreach ($patterns as $pattern) {
            foreach (glob($pattern) as $file) {
                require_once $file;
            }
        }
    }
    /**
     * Check if an extension exists
     * @param $alias
     * @return bool
     */
    public static function hasExtension($alias)
    {
        return isset(self::$extensions[$alias]);
    }
    /**
     * Add an extension
     * @param ExtensionInterface $extension
     * @throws \RuntimeException
     */
    public static function addExtension(ExtensionInterface $extension)
    {
        if (!self::hasExtension($alias = $extension->getAlias())) {
            self::$extensions[$alias] = $extension;
        } else {
            throw new RuntimeException("Extension with alias '$alias' has already been added");
        }
    }
    /**
     * Replace an extension
     * @param ExtensionInterface $extension
     */
    public static function replaceExtension(ExtensionInterface $extension)
    {
        self::$extensions[$extension->getAlias()] = $extension;
    }
    /**
     * Remove a specified extension
     * @param $alias
     */
    public static function removeExtension($alias)
    {
        if (self::hasExtension($alias)) {
            unset(self::$extensions[$alias]);
        }
    }
    /**
     * Remove all extensions
     */
    public static function clearExtensions()
    {
        self::$extensions = array();
    }
    /**
     * Check if a compiler pass exists
     * @param $name
     * @return bool
     */
    public static function hasCompilerPass($name)
    {
        return isset(self::$compilerPasses[$name]);
    }
    /**
     * Add a compiler pass
     * @param CompilerPassInterface $compilerPass
     * @param string                $type
     * @throws \RuntimeException
     */
    public static function addCompilerPass(
        CompilerPassInterface $compilerPass,
        $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ) {
        if (!self::hasCompilerPass($name = get_class($compilerPass))) {
            self::$compilerPasses[$name] = array(
                $compilerPass,
                $type
            );
        } else {
            throw new RuntimeException("Compiler pass of classname '$name' has already been added");
        }
    }
    /**
     * Replace a compiler pass
     * @param CompilerPassInterface $compilerPass
     * @param string                $type
     */
    public static function replaceCompilerPass(
        CompilerPassInterface $compilerPass,
        $type = PassConfig::TYPE_BEFORE_OPTIMIZATION
    ) {
        self::$compilerPasses[get_class($compilerPass)] = array(
            $compilerPass,
            $type
        );
    }
    /**
     * Remove a specified compiler pass
     * @param $name
     */
    public static function removeCompilerPass($name)
    {
        if (self::hasCompilerPass($name)) {
            unset(self::$compilerPasses[$name]);
        }
    }
    /**
     * Clear the compiler passes
     */
    public static function clearCompilerPasses()
    {
        self::$compilerPasses = array();
    }
    /**
     * Create a container based on the static configuration of this class
     * @param array $parameters
     * @param bool  $servicesLocation
     * @return ContainerBuilder
     */
    public static function createContainer(
        array $parameters = array(),
        $servicesLocation = false
    ) {
        $container = new ContainerBuilder();

        foreach (self::$extensions as $extension) {
            $container->registerExtension($extension);
        }

        foreach (self::$compilerPasses as $compilerPass) {
            $container->addCompilerPass($compilerPass[0], $compilerPass[1]);
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
    /**
     * Dump a container
     * @param Container $container
     * @param           $class
     * @param           $location
     * @throws \RuntimeException
     */
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
