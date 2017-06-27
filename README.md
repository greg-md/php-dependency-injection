# Greg PHP Dependency Injection

[![StyleCI](https://styleci.io/repos/95591536/shield?style=flat)](https://styleci.io/repos/29315729)
[![Build Status](https://travis-ci.org/greg-md/php-dependency-injection.svg)](https://travis-ci.org/greg-md/php-dependency-injection)
[![Total Downloads](https://poser.pugx.org/greg-md/php-dependency-injection/d/total.svg)](https://packagist.org/packages/greg-md/php-dependency-injection)
[![Latest Stable Version](https://poser.pugx.org/greg-md/php-dependency-injection/v/stable.svg)](https://packagist.org/packages/greg-md/php-dependency-injection)
[![Latest Unstable Version](https://poser.pugx.org/greg-md/php-dependency-injection/v/unstable.svg)](https://packagist.org/packages/greg-md/php-dependency-injection)
[![License](https://poser.pugx.org/greg-md/php-dependency-injection/license.svg)](https://packagist.org/packages/greg-md/php-dependency-injection)

Greg Dependency Injection provides you a lightweight, but powerful IoC Container
that allows you to standardize and centralize the way objects are constructed in your application.

# Table of Contents

* [Requirements](#requirements)
* [Installation](#installation)
* [How It Works](#how-it-works)
    * [Inject](#inject)
    * [Get](#get)
    * [Load](#load)
    * [Call](#call)
    * [Autoload](#autoload)
* [License](#license)
* [Huuuge Quote](#huuuge-quote)

# Requirements

* PHP Version `^7.1`

# Installation

`composer require greg-md/php-dependency-injection`

# How It Works

All you need to start using the [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection) technique,
is to instantiate an IoC Container and inject objects in it.

```php
$ioc = new \Greg\DependencyInjection\IoCContainer();
```

### Inject

```php
$ioc->inject('foo', Foo::class);

$ioc->inject('bar', new Bar());
```

**You can also inject in a more elegant way, using the object name as abstract.**

```php
$ioc->register(new Foo());
```

The previous example is equivalent with:

```php
$ioc->inject(Foo::class, new Foo());
```

**Customise the way your objects will be instantiated**

```php
$ioc->inject('redis.client', function() {
    $redis = new \Redis();

    $redis->connect();

    return $redis;
});
```

### Get

The next example will return null if the object is not injected in the IoC Container.

```php
$foo = $ioc->get('foo');
```

The next example will throw an exception if parameter is not injected in the IoC Container.

```php
$foo = $ioc->expect('foo');
```

### Load

In a real application to take advantage of what's best from Dependency Injection technique,
you may want to instantiate objects with dependencies from the IoC Container without defining them manually.
The best way to do that is to inject objects with it's names or it's strategies names as abstracts.

Let say we have the `Foo` class that requires a `BarStrategy` class.

```php
class Foo
{
    private $bar;
    
    public function __construct(BarStrategy $bar)
    {
        $this->bar = $bar;
    }
}
```

What we do is inject the `BarStrategy` into the IoC Container and load the `Foo` class from it.

> `BarStrategy` is an `interface`,
> so, we don't break the [SOLID](https://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) principles.

```php
$ioc->inject(BarStrategy::class, function() {
    return new Bar();
});

$foo = $ioc->load(Foo::class);
```

Sometimes you may want to redefine one or more dependencies of a class when loading it from the IoC Container.

```php
class Foo
{
    private $bar;
    
    private $baz;
    
    public function __construct(BarStrategy $bar, BazStrategy $bar)
    {
        $this->bar = $bar;
        
        $this->baz = $baz;
    }
}
```

```php
$ioc->inject(BarStrategy::class, function() {
    return new Bar();
});

$ioc->inject(BazStrategy::class, function() {
    return new Baz();
});
```

You can easily do it by defining those dependencies next after the class name in `load` method.

```php
$foo = $ioc->load(Foo::class, new CustomBaz());
```

Or, load with arguments as array.

```php
$ioc->loadArgs([$someObject, 'method'], ...[new CustomBaz()]);
```

The previous example will instantiate `BarStrategy` from the IoC Container, which is `Bar` class
and for `BazStrategy` it will set the `CustomBaz` defined in the `load` method.

### Call

You can call a callable with arguments injected in the Ioc Container
the same way as [loading classes](#load).

```php
$ioc->call(function(int $foo, Bar $bar) {
    // $bar will be injected from the Ioc Container.
}, 10);
```

Or call using arguments.

```php
$ioc->callArgs([$someObj, 'someMethod'], ...$arguments);
```

### Autoload

You can autoload some classes by defining their prefixes/suffixes as abstracts.

```php
$ioc->addPrefixes('Foo\\');

$ioc->addSuffixes('Controller');

$bar = $ioc->get(\Foo\BarController::class);
```

# License

MIT © [Grigorii Duca](http://greg.md)

# Huuuge Quote

![I fear not the man who has practiced 10,000 programming languages once, but I fear the man who has practiced one programming language 10,000 times. &copy; #horrorsquad](http://greg.md/huuuge-quote-fb.jpg)
