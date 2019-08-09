<?php

namespace MikeGarde\LaravelDataDogBatched;

use DataDog;
use Doctrine\DBAL\Logging\SQLLogger;
use LaravelDoctrine\ORM\Loggers\Formatters\FormatQueryKeywords;
use LaravelDoctrine\ORM\Loggers\Formatters\ReplaceQueryParams;
use Psr\Log\LoggerInterface as Log;

class DoctrineFileLogger implements SQLLogger
{
	/**
	 * @var Log
	 */
	protected $logger;

	/**
	 * @var FormatQueryKeywords
	 */
	protected $formatter;

	/**
	 * @var float
	 */
	protected $start;

	/**
	 * @var string
	 */
	protected $query;

	/**
	 * @param Log $logger
	 */
	public function __construct(Log $logger)
	{
		$this->logger    = $logger;
		$this->formatter = new FormatQueryKeywords(new ReplaceQueryParams);
	}

	/**
	 * Logs a SQL statement somewhere.
	 *
	 * @param string     $sql    The SQL to be executed.
	 * @param array|null $params The SQL parameters.
	 * @param array|null $types  The SQL parameter types.
	 *
	 * @return void
	 */
	public function startQuery($sql, array $params = null, array $types = null)
	{
		$this->start = microtime(true);
		$this->query = $this->formatter->format($sql, $params);
	}


	/**
	 * Marks the last started query as stopped. This can be used for timing of queries.
	 * @return void
	 */
	public function stopQuery()
	{
		//$this->logger->debug($this->getQuery(), [$this->getExecutionTime()]);
		$c = strtoupper(substr($this->getQuery(), 0, 1));

		switch ($c)
		{
			case 'S':
				$tag = ['action' => 'select'];
				break;
			case 'I':
				$tag = ['action' => 'insert'];
				break;
			case 'U':
				$tag = ['action' => 'update'];
				break;
			case 'D':
				$tag = ['action' => 'delete'];
				break;
			default:
				$tag = ['action' => 'unknown'];
		}

		DataDog::increment('sql', 1, $tag);
		DataDog::microtiming('sql.timing', $this->getExecutionTime(), 1, $tag);
	}

	/**
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * @return float
	 */
	protected function getExecutionTime()
	{
		return microtime(true) - $this->start;
	}
}
