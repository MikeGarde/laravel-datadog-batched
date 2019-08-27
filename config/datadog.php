<?php

return [
	'enabled'         => true,
	'sampleRate'      => 1,
	'autoRecord'      => true,
	'fullUrl'         => false,
	'routes'          => true,
	'methods'         => true,
	'statusCodes'     => 'group', # true / false / 'group' # 2xx instead of 200 and 201
	'routesInSQL'     => true,
	'prefix'          => 'app.example',
	'global_tags'     => [],
	'api_key'         => null, # Leave Blank for UDP & to use Agent Installed on Machine
	'application_key' => null, # Leave Blank ^^^^
	'datadog_host'    => 'https://app.datadoghq.com',
	'statsd_server'   => 'localhost',
	'statsd_port'     => 8125,
	'transport'       => 'UDP',
];
