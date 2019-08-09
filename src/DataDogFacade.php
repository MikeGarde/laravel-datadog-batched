<?php

namespace MikeGarde\LaravelDataDogBatched;

use Illuminate\Support\Facades\Facade;

class DataDogFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'DataDog';
	}
}
