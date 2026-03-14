# Laravel Module Support

Composer-installable module support for Laravel applications.

## What this package does

This package moves your **module framework** into `vendor/`, while your actual app modules can still live in your Laravel app (by default in `modules/`).

So after installation you get:

- module discovery via `modules/*/module.php`
- enable / disable state handling
- dependency checks between modules
- artisan commands like `module:list`, `module:enable`, `module:disable`, `module:create`
- auto-registration of enabled module service providers

## Install

### 1. Require the package

If you publish it to Packagist or a private Git repository:

```bash
composer require vbk-solutions/laravel-module-support
```

For local development through a path repository:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "./packages/laravel-module-support",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "vbk-solutions/laravel-module-support": "*"
  }
}
```

Then run:

```bash
composer update vbk-solutions/laravel-module-support
```

### 2. Publish the config

```bash
php artisan vendor:publish --tag=module-support-config
```

### 3. Make sure your app autoloads your modules

Add this to your Laravel app `composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "modules/"
    }
  }
}
```

Then run:

```bash
composer dump-autoload
```

## Config

Published `config/modules.php`:

```php
return [
    'path' => base_path('modules'),
    'status_repo' => base_path('bootstrap/cache/module-statuses.php'),
];
```

## Example module definition

`modules/Blog/module.php`

```php
<?php

namespace Modules\Blog;

use VBKSolutions\LaravelModuleSupport\Data\ModuleDefinition;
use Modules\Blog\Providers\BlogServiceProvider;

return new ModuleDefinition(
    name: 'Blog',
    version: '1.0.0',
    description: 'Blog module',
    author: 'VBK Solutions',
    dependencies: [],
    providers: [
        BlogServiceProvider::class,
    ],
);
```

## Commands

```bash
php artisan module:list
php artisan module:status Blog
php artisan module:enable Blog
php artisan module:disable Blog
php artisan module:depends-on Blog
php artisan module:create Blog
php artisan module:test Blog
```

## Notes

- The package itself belongs in `vendor/`.
- Your app modules stay outside `vendor/` by default, which is safer and easier to maintain.
- If you want, you can change the modules path in `config/modules.php`.
