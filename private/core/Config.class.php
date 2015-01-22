<?php

namespace core;

class Config
{
	static private $coreVars 	= array();
	static private $guardVars 	= array();
	static private $hostsVars 	= array();
	static private $configVars 	= array();
	static private $instance 	= null;

	static function Init()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new Config();
		}
	}

	private function __construct()
	{
		$this->loadCoreVars();
	}

	function loadCoreVars()
	{
		if(empty(self::$coreVars))
		{
			$vars = \Core\Tools::flatten(json_decode(file_get_contents(__DIR__.'/config/core.json'), true));
			
			if(empty($vars))
			{
				\Core\Core::throwError(500, 'CoreConfig syntax error');
			}
			else
			{
				self::$coreVars = $vars;
			}
		}
	}

	static function getCore($key)
	{
		return isset(self::$coreVars[$key]) ? self::$coreVars[$key] : null;
	}

	static function getAllCore()
	{
		return self::$coreVars;
	}
}