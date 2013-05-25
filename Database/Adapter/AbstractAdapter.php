<?php

namespace Database\Adapter;

abstract class AbstractAdapter
{
	
	protected $config;
	
	public function __construct($config)
	{
		$this->config = $config;
	}
	
	public abstract function save($table, array $data);
	
	public abstract function saveIfNotExists($table, array $data);
	
	public abstract function get($table, $limit = NULL, $offset = NULL);
	
	public abstract function getSingle($table, array $data);
	
}