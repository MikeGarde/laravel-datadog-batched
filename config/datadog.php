<?php

return [
	'enabled'         => true,
	'batched'         => true,
	'prefix'          => 'app.example',
	'global_tags'     => [],
	'api_key'         => null, # Leave Blank for UDP & to use Agent Installed on Machine
	'application_key' => null, # Leave Blank ^^^^
	'datadog_host'    => 'https://app.datadoghq.com',
	'statsd_server'   => 'localhost',
	'statsd_port'     => 8125,
	'disable_url_tag' => false,
	'transport'       => 'UDP',
];
