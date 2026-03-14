<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules path
    |--------------------------------------------------------------------------
    |
    | This is the path where your application modules live. By default they
    | stay in the Laravel root so your custom modules remain part of the app,
    | while the module-support framework itself is installed in vendor.
    |
    */
    'path' => base_path('modules'),

    /*
    |--------------------------------------------------------------------------
    | Module state repository
    |--------------------------------------------------------------------------
    |
    | Enabled / disabled states are written to a simple PHP file. You can swap
    | this later for a database-backed repository if you want.
    |
    */
    'status_repo' => base_path('bootstrap/cache/module-statuses.php'),
];
