<?php

namespace MikeGarde\LaravelDataDogBatched;

use Auth;
use Closure;
use LaravelDataDog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
		LaravelDataDog::startTime();

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
		if (!config('datadog.autoRecord'))
		{
			return;
		}

		$tags = [];

		if (!Auth::id())
		{
			$user = 0;
		}
		else
		{
			$user = $request->getAuthenticatedUserId();
		}

		if (config('datadog.fullUrl'))
		{
			$tags['fullUrl'] = implode('/', $request->segments());
		}

		if (config('datadog.routes'))
		{
			$tmp           = app('router')->getCurrentRoute();
			$tmp           = method_exists($tmp, 'uri') ? $tmp->uri() : 'unknown';
			$tags['route'] = ($tmp) ?: '';
		}

		if (config('datadog.methods'))
		{
			$tags['method'] = $request->getMethod();
		}

		if (config('datadog.statusCodes') === 'group')
		{
			$tags['status'] = substr($response->status(), 0, 1) . 'xx';
		}
		elseif (config('datadog.statusCodes'))
		{
			$tags['status'] = $response->status();
		}

		LaravelDataDog::set('uniques', $user);
		LaravelDataDog::increment('request', config('datadog.sampleRate'), $tags);
		LaravelDataDog::recordTiming('timing', config('datadog.sampleRate'), $tags);
		LaravelDataDog::flush();
	}
}
