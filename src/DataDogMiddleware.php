<?php

namespace MikeGarde\LaravelDataDogBatched;

use Auth;
use Closure;
use DataDog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;

class DataDogMiddleware
{
	/**
	 * @param         $request
	 * @param Closure $next
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function handle($request, Closure $next)
	{
		DataDog::startTime();

		return $next($request);
	}

	/**
	 * Perform any final actions for the request lifecycle.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Symfony\Component\HttpFoundation\Response $response
	 *
	 * @return void
	 */
	public function terminate(Request $request, Response $response)
	{
		if (!Auth::id())
		{
			$user     = 0;
			$resource = ['api' => 'unauthorized'];
		}
		else
		{
			$user     = $request->getAuthenticatedUserId();
			$resource = explode('?', $request->getRequestUri());
			$resource = trim(str_replace('/', '.', $resource[0]), '.');
			$resource = [
				'api'    => $resource,
				'method' => $request->getMethod(),
			];
		}

		$time = microtime(true);

		DataDog::increment('request', 1, $resource);
		DataDog::set('uniques', $user);
		DataDog::recordTiming('timing', 1, $resource);
	}
}
