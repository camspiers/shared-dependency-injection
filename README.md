# Shared Dependency Injection

[![Build Status](https://travis-ci.org/camspiers/shared-dependency-injection.png?branch=master)](https://travis-ci.org/camspiers/shared-dependency-injection)

Provides the ability to build a symfony dependency injection container which multiple unrelated module or libraries add their extensions and compiler passes to.

# Installation (with composer)

	$ composer require camspiers/shared-dependency-injection:~0.2

# Usage

```php
use Camspiers\DependencyInjection\SharedContainerFactory;

SharedContainerFactory::requireExtensionConfigs(__DIR__ . '/..');

SharedContainerFactory::dumpContainer(
    $container = SharedContainerFactory::createContainer(
        array(),
        __DIR__ . '/services.yml'
    ),
    'SharedContainer',
    __DIR__
);
```

# Unit testing

	shared-dependency-injection/ $ composer install --dev
	shared-dependency-injection/ $ vendor/bin/phpunit
