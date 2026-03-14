# Laravel Module Support

Composer-installable module support for Laravel applications.

That gives you a clean split:

- **this package** provides module discovery, state management, commands, dependency checks and provider loading
- **your app** keeps ownership of the actual business modules

---

## What this package does

The package provides:

- module discovery through `modules/*/module.php`
- enable / disable state handling
- dependency checks between modules
- automatic loading of service providers for enabled modules
- artisan commands such as:
  - `module:list`
  - `module:status`
  - `module:enable`
  - `module:disable`
  - `module:depends-on`
  - `module:create`
  - `module:test`

This package does **not** move your own modules into `vendor/`.
Only the support framework itself lives in `vendor/`.

---

## Requirements

- PHP `^8.2`
- Laravel `^11.0 | ^12.0`

---

## Installation

### Install from Packagist

When a stable release is available:

```bash
composer require vbk-solutions/laravel-module-support
```


---

## Quick start for a fresh Laravel app

After requiring the package, do the following.

### 1. Publish the config

```bash
php artisan vendor:publish --tag=module-support-config
```

This creates:

```txt
config/modules.php
```

Default contents:

```php
<?php

return [
    'path' => base_path('modules'),
    'status_repo' => base_path('bootstrap/cache/module-statuses.php'),
];
```

### 2. Make sure your Laravel app autoloads application modules

In the **root** `composer.json` of your Laravel app, make sure this exists:

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

### 3. Create the `modules` directory if it does not exist yet

```bash
mkdir -p modules
```

### 4. Create or generate your first module

You can generate one with:

```bash
php artisan module:create Blog
```

### 5. Enable the module

```bash
php artisan module:enable Blog
```

### 6. Check status

```bash
php artisan module:list
php artisan module:status Blog
```

---

## How a module must be structured

Each module directory must contain a `module.php` file.

Example:

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
    dependencies: [
        'Auth',
        'Core',
    ],
    providers: [
        BlogServiceProvider::class,
    ],
);
```

### Important

Your `module.php` file must return:

```php
new ModuleDefinition(...)
```

and it must import:

```php
use VBKSolutions\LaravelModuleSupport\Data\ModuleDefinition;
```

---

## Configuration

Published config file:

```php
<?php

return [
    'path' => base_path('modules'),
    'status_repo' => base_path('bootstrap/cache/module-statuses.php'),
];
```

### `path`

The directory that contains your application modules.

Default:

```php
base_path('modules')
```

### `status_repo`

The PHP file where enabled / disabled module states are stored.

Default:

```php
base_path('bootstrap/cache/module-statuses.php')
```

---

## Available commands

```bash
php artisan module:list
php artisan module:status Blog
php artisan module:enable Blog
php artisan module:disable Blog
php artisan module:depends-on Blog
php artisan module:create Blog
php artisan module:test Blog
```

---

## Typical installation flow on a server

For a clean Laravel installation, the normal flow is:

```bash
composer require vbk-solutions/laravel-module-support
php artisan vendor:publish --tag=module-support-config
composer dump-autoload
mkdir -p modules
php artisan module:create Blog
php artisan module:enable Blog
php artisan module:list
```

---

## Troubleshooting

### `Module config file must return an instance of ModuleDefinition.`

This means one of your `modules/*/module.php` files is not returning the correct class.

Check that the file:

- returns `new ModuleDefinition(...)`
- imports `VBKSolutions\LaravelModuleSupport\Data\ModuleDefinition`
- does **not** return an array
- does **not** import an old class from your previous internal module system

Correct:

```php
use VBKSolutions\LaravelModuleSupport\Data\ModuleDefinition;
```

### Package installs but commands do not work

Check:

- the package is installed in `vendor/`
- `config/modules.php` exists or defaults are being used
- your Laravel app autoloads the `Modules\\` namespace
- your module directory exists
- your modules have valid `module.php` files

### New module classes are not found

Run:

```bash
composer dump-autoload
```

---

## Notes

- Your application modules stay in your Laravel project by default
- You can change the modules path through `config/modules.php`
- The module state file is created when module states are written

---

## License

MIT
