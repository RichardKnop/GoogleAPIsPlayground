<?php

namespace Database\Adapter;

class Factory
{
	
	public static function manufacture($config)
	{
		if (!file_exists(APPLICATION_PATH . '/Database/Adapter/' . $config['database']['adapter'] . '.php')) {
			throw new \Exception('Adapter not found');
		}
		$adapterClass = '\\Database\\Adapter\\' . $config['database']['adapter'];
		return new $adapterClass($config);
	}
	
}