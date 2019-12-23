# Php DI Container

## About

*Simple PSR-11 compliant dependency injection container.*

---
Around the the web there are many examples to be found for simple PHP DI Containers and Resolvers.
From all the inspiration I have written a version to my own personal liking.
Feel free to use it in your project or take it as inspiration for your own version.   

For now only the object definition has been implemented but more will follow.


## Getting started
```bash
    $ composer require Jascha030/dic
```

## Usage

Short example:

```php
$container = new PsrServiceContainer(); // Instantiate container

$userService = $container->get(UserService::class); // Get service or class instance.

$userService->printUserName(); // Outputs: My name is Jeff
```

Full example in [src/example.php](https://github.com/jascha030/DI-Container/blob/master/src/example.php)
