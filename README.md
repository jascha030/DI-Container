# Php DI Container

## About

Simple PSR-11 compliant dependency injection container.

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

Full example in `src/example.php`
