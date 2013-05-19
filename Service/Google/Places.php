<?php

namespace Service\Google;

use Service\Google\AbstractService;

class Places extends AbstractService
{
	
	public function __construct($config)
	{
		parent::__construct($config, 'places');
	}
	
}
