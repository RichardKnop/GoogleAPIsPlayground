<?php

namespace Collection;

class AbstractCollection
{
	
	protected $entities = array();
	
	public function add($entity)
	{
		$this->entities[] = $entity;
	}
	
	public function getAll()
	{
		return $this->entities;
	}
	
	public function getAsJson()
	{
		$arr = array();
		foreach ($this->entities as $entity) {
			$arr[] = $entity->toArray();
		}
		return json_encode($arr);
	}
	
}