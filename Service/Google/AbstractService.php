<?php

namespace Service\Google;

use Guzzle\Http\Client;

class AbstractService
{
	
	protected $config;
	protected $client;
	
	public function __construct($config, $serviceConfigKey)
	{
		$this->config = $config;
		$this->client = new Client($this->config[$serviceConfigKey]['baseUrl']);
	}
	
}
