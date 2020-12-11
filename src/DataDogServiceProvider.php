<?php

namespace MikeGarde\LaravelDataDogBatched;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class DataDogServiceProvider extends ServiceProvider
{
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes(
			[__DIR__ . '/../config/datadog.php' => config_path('datadog.php')],
			'datadog-config');
		$this->app->bind('LaravelDataDog', DataDogHelper::class);
	}

	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../config/datadog.php', 'datadog');

		$this->app->singleton('LaravelDataDog', function () {

			return new \MikeGarde\LaravelDataDogBatched\DataDogHelper([
				'host'                 => config('datadog.statsd_server'),
				'port'                 => config('datadog.statsd_port'),
				'socket_path'          => null, # supported above agent v6
				'global_tags'          => config('datadog.global_tags'),
				'api_key'              => config('datadog.api_key'),
				'app_key'              => config('datadog.application_key'),
				'curl_ssl_verify_host' => 2,
				'curl_ssl_verify_peer' => 1,
				'datadog_host'         => config('datadog.datadog_host'),
			]);
		});
	}
}
