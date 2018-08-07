## Laravel Runtime Errors

This package parses your Laravel project, and finds errors before they occur in production.

The following artisan commands are added:

```
 runtime-errors
  runtime-errors:route-calls                    Checks your route calls to see if they map to a registered named route
  runtime-errors:route-definitions              Checks your route definitions to see if they point to an existing controller action
  runtime-errors:view-calls                     Checks your view calls to see if they map to a file that exists.
```

Example output:
 
![Example output](resources/example-output.png)

## Installation

```
composer require --dev juddling/laravel-route-checker
```

### Laravel Version <= 5.4

Add the service provider to `config/app.php`:

```
\Juddling\RouteChecker\RouteCheckerServiceProvider::class,
```

## Contributing
Install from source:
```
composer update juddling/laravel-route-checker --prefer-source
```