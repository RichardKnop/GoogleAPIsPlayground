<?php

namespace Entity;

class AbstractEntity
{
	
	public function toArray()
	{
		$arr = array();
		foreach (get_object_vars($this) as $property => $value) {
			$arr[$property] = $value;
		}
		return $arr;
	}
	
}