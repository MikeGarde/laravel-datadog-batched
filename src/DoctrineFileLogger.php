<?php

namespace MikeGarde\LaravelDataDogBatched;

use DataDog;
use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface as Log;

class DoctrineFileLogger implements SQLLogger
{
	/**
	 * @var Log
	 */
	protected $logger;

	/**
	 * @var float
	 */
	protected $start;

	/**
	 * @var string
	 */
	protected $query;

	/**
	 * @var string
	 */
	private $category;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param Log $logger
	 */
	public function __construct(Log $logger)
	{
		$this->logger = $logger;
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
		$this->query = $sql;
		$this->identifyCommand();
	}


	/**
	 * Marks the last started query as stopped. This can be used for timing of queries.
	 * @return void
	 */
	public function stopQuery()
	{
		$tmp   = app('router')->getCurrentRoute();
		$tmp   = method_exists($tmp, 'uri') ? $tmp->uri() : 'unknown';
		$route = ($tmp) ?: '';

		$tag = [
			'type'     => $this->type,
			'route'    => $route,
		];
		DataDog::increment('sql', 1, $tag);
		DataDog::microtiming('sql.timing', $this->getExecutionTime(), 1, $tag);

		// Some users may enable DataDog on Workers, if so lets periodically flush the buffer to DataDog
		if (rand(1,100) <= 5)
		{
			DataDog::flush();
		}
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

	protected function identifyCommand()
	{
		$sql = strtolower($this->query);

		$t['create']    = ['DDL', strpos($sql, 'create')];
		$t['drop']      = ['DDL', strpos($sql, 'drop')];
		$t['alter']     = ['DDL', strpos($sql, 'alter')];
		$t['truncate']  = ['DDL', strpos($sql, 'truncate')];
		$t['comment']   = ['DDL', strpos($sql, 'comment')];
		$t['rename']    = ['DDL', strpos($sql, 'rename')];
		$t['select']    = ['DML', strpos($sql, 'select')];
		$t['insert']    = ['DML', strpos($sql, 'insert')];
		$t['update']    = ['DML', strpos($sql, 'update')];
		$t['delete']    = ['DML', strpos($sql, 'delete')];
		$t['grant']     = ['DCL', strpos($sql, 'grant')];
		$t['revoke']    = ['DCL', strpos($sql, 'revoke')];
		$t['commit']    = ['TCL', strpos($sql, 'commit')];
		$t['rollback']  = ['TCL', strpos($sql, 'rollback')];
		$t['savepoint'] = ['TCL', strpos($sql, 'savepoint')];
		$t['set']       = ['TCL', strpos($sql, 'set')];

		foreach ($t as $key => $value)
		{
			if ($value[1] === false)
			{
				unset($t[ $key ]);
			}
		}

		if ($t)
		{
			asort($t);

			$this->type     = key($t);
			$this->category = $t[ $this->type ][0];
		}
		else
		{
			$this->category = 'unknown';
			$this->type     = 'unknown';
		}
	}
}
