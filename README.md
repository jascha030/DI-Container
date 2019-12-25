# Php DI Container

[![Latest Stable Version](https://poser.pugx.org/jascha030/dic/v/stable)](https://packagist.org/packages/jascha030/dic)
[![Total Downloads](https://poser.pugx.org/jascha030/dic/downloads)](https://packagist.org/packages/jascha030/dic)
[![Latest Unstable Version](https://poser.pugx.org/jascha030/dic/v/unstable)](https://packagist.org/packages/jascha030/dic)
[![License](https://poser.pugx.org/jascha030/dic/license)](https://packagist.org/packages/jascha030/dic)
[![composer.lock](https://poser.pugx.org/jascha030/dic/composerlock)](https://packagist.org/packages/jascha030/dic)

## About

*Simple PSR-11 compliant dependency injection container.*

---
Around the the web there are many examples to be found for simple PHP DI Containers and Resolvers.
From all the inspiration I have written a version to my own personal liking.
Feel free to use it in your project or take it as inspiration for your own version.   

For now only the object definition has been implemented but more will follow.


## Getting started
```bash
    $ composer require jascha030/dic
```

## Usage

Short example:

Class UserService with dependency User.

```php
class UserService
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function printUserName()
    {
        echo "My name is " . $this->user->name;
    }
}
```

```php
$container = new PsrServiceContainer(); // Instantiate container

$userService = $container->get(UserService::class); // Get service or class instance.

$userService->printUserName(); // Outputs: My name is Jeff
```

Full example in [src/example.php](https://github.com/jascha030/DI-Container/blob/master/src/example.php)
