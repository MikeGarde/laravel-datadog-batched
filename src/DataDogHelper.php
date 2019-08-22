<?php

namespace MikeGarde\LaravelDataDogBatched;

use DataDog\BatchedDogStatsd;

class DataDogHelper
{
	/**
	 * @var BatchedDogStatsd
	 */
	private $dd;

	/**
	 * @var float
	 */
	private $start;

	/**
	 * DataDogHelper constructor.
	 *
	 * @param BatchedDogStatsd $dd
	 */
	public function __construct(BatchedDogStatsd $dd)
	{
		$this->dd = $dd;
	}

	/**
	 * Record when the middleware first loaded this
	 */
	public function startTime()
	{
		$this->start = microtime(true);
	}

	/**
	 * @return float
	 */
	protected function getExecutionTime()
	{
		return microtime(true) - $this->start;
	}

	/**
	 * @param       $stat
	 * @param float $sampleRate
	 * @param       $tags
	 */
	public function recordTiming($stat, $sampleRate = 1.0, $tags)
	{
		$this->microtiming($stat, $this->getExecutionTime(), $sampleRate, $tags);
	}

	/**
	 * @param       $data
	 * @param float $sampleRate
	 * @param null  $tags
	 */
	private function send($data, $sampleRate = 1.0, $tags = null)
	{
		if (!config('datadog.enabled'))
		{
			return;
		}

		foreach ($data as $key => $value)
		{
			$newData = [config('datadog.prefix') . '.' . $key => $value];
		}

		$this->dd->send($newData, $sampleRate, $tags);
	}

	/**
	 * @param       $stats
	 * @param int   $delta
	 * @param float $sampleRate
	 * @param null  $tags
	 */
	private function updateStats($stats, $delta = 1, $sampleRate = 1.0, $tags = null)
	{
		if (!is_array($stats))
		{
			$stats = [$stats];
		}
		$data = [];
		foreach ($stats as $stat)
		{
			$data[ $stat ] = "$delta|c";
		}
		$this->send($data, $sampleRate, $tags);
	}

	/**
	 * Log timing information
	 *
	 * @param string       $stat       The metric to in log timing info for.
	 * @param float        $time       The elapsed time (ms) to log
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 */
	public function timing($stat, $time, $sampleRate = 1.0, $tags = null)
	{
		$this->send([$stat => "$time|ms"], $sampleRate, $tags);
	}

	/**
	 * A convenient alias for the timing function when used with micro-timing
	 *
	 * @param string       $stat       The metric name
	 * @param float        $time       The elapsed time to log, IN SECONDS
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 **/
	public function microtiming($stat, $time, $sampleRate = 1.0, $tags = null)
	{
		$this->timing($stat, $time * 1000, $sampleRate, $tags);
	}

	/**
	 * Gauge
	 *
	 * @param string       $stat       The metric
	 * @param float        $value      The value
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 **/
	public function gauge($stat, $value, $sampleRate = 1.0, $tags = null)
	{
		$this->send([$stat => "$value|g"], $sampleRate, $tags);
	}

	/**
	 * Histogram
	 *
	 * @param string       $stat       The metric
	 * @param float        $value      The value
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 **/
	public function histogram($stat, $value, $sampleRate = 1.0, $tags = null)
	{
		$this->send([$stat => "$value|h"], $sampleRate, $tags);
	}

	/**
	 * Distribution
	 *
	 * @param string       $stat       The metric
	 * @param float        $value      The value
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 **/
	public function distribution($stat, $value, $sampleRate = 1.0, $tags = null)
	{
		$this->send([$stat => "$value|d"], $sampleRate, $tags);
	}

	/**
	 * Set
	 *
	 * @param string       $stat       The metric
	 * @param float        $value      The value
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 **/
	public function set($stat, $value, $sampleRate = 1.0, $tags = null)
	{
		$this->send([$stat => "$value|s"], $sampleRate, $tags);
	}

	/**
	 * Increments one or more stats counters
	 *
	 * @param string|array $stats      The metric(s) to increment.
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 * @param int          $value      the amount to increment by (default 1)
	 **/
	public function increment($stats, $sampleRate = 1.0, $tags = null, $value = 1)
	{
		$this->updateStats($stats, $value, $sampleRate, $tags);
	}

	/**
	 * Decrements one or more stats counters.
	 *
	 * @param string|array $stats      The metric(s) to decrement.
	 * @param float        $sampleRate the rate (0-1) for sampling.
	 * @param array|string $tags       Key Value array of Tag => Value, or single tag as string
	 * @param int          $value      the amount to decrement by (default -1)
	 **/
	public function decrement($stats, $sampleRate = 1.0, $tags = null, $value = -1)
	{
		if ($value > 0)
		{
			$value = -$value;
		}
		$this->updateStats($stats, $value, $sampleRate, $tags);
	}

	/**
	 * Required for UDP
	 */
	public function flush()
	{
		if (config('datadog.enabled'))
		{
			$this->dd->flush_buffer();
		}
	}
}
