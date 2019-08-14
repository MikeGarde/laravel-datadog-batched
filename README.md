## Install

### config/app.php

```php
return [
    'providers' => [
        MikeGarde\LaravelDataDogBatched\DataDogServiceProvider::class,
    ],
    
    'aliases' => [
        'DataDog' => MikeGarde\LaravelDataDogBatched\DataDogFacade::class,
    ],
];
```

### app/Http/Kernel.php

```php
	protected $middleware = [
        \MikeGarde\LaravelDataDogBatched\DataDogMiddleware::class,
	];
```

### config/datadog.php

```bash
php artisan vendor:publish --provider="MikeGarde\LaravelDataDogBatched\DataDogServiceProvider"
```

Review the config and name the `prefix` appropriately

Tip: enable or disable based on environment

```php
return [
	'enabled'         => (env('APP_ENV') === 'prod' || env('APP_ENV') === 'qa'),
	'prefix'          => 'app.APIv2',
];
```

### config/doctrine.php

If desired to log SQL interactions and duration 
```php
return [
    'logger' => \MikeGarde\LaravelDataDogBatched\FileLogger::class,
];
```
