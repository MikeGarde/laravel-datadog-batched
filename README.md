## Install

### config/app.php

Add the following

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

Add the config to your repo

```bash
php artisan vendor:publish --provider="MikeGarde\LaravelDataDogBatched\DataDogServiceProvider"
```

Review the config and name the `prefix` appropriately

### config/doctrine.php

If desired to log SQL interactions and duration 
```php
return [
    'logger' => \MikeGarde\LaravelDataDogBatched\FileLogger::class,
];
```
