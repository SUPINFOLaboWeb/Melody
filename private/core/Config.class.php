<?php

namespace core;

class Config
{
	static private $core_config 	= array();
	static private $app_config 		= array();
	static private $instance 		= null;

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
		if(empty(self::$core_config))
		{
			if(false === $cache = \Core\Cache::get('coreconfig', 'core', 'on_demand'))
			{
				$vars = \Core\Tools::flatten(json_decode(file_get_contents(__DIR__.'/config/core.json'), true));
			
				if(empty($vars))
				{
					\Core\Core::throwError(500, 'CoreConfig syntax error');
				}
				else
				{
					self::$core_config = $vars;
					\Core\Cache::create('coreconfig', 'core', $vars, 'on_demand');
				}
			}
			else
			{
				self::$core_config = $cache;
			}
		}
	}

	static function loadFor($apps)
	{	
		if(empty(self::$app_config))
		{
			if(false === $cache = \Core\Cache::get('appconfig', join(DIRECTORY_SEPARATOR, array_filter($apps)), 'on_demand'))
			{
				$appspath = array('');
				$buffer = '';
				foreach ($apps as $app) 
				{
					if(!empty($app))
					{
						$buffer .= DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$app;
						$appspath[] = $buffer;
					}
				}

				foreach ($appspath as $currentpath) 
				{
					$app_config = array();
					$path = ROOT.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'www'.$currentpath.DIRECTORY_SEPARATOR.'config';

					$configfiles = is_dir($path) ? Tools::file_get_contents_loop($path) : array();
					foreach ($configfiles as $name => $content)
					{
						$app_config[$name] = json_decode($content, true);
					}
					$app_config = Tools::flatten($app_config);
					self::$app_config = array_merge(self::$app_config, $app_config);
				}

				\Core\Cache::create('appconfig', join(DIRECTORY_SEPARATOR, array_filter($apps)), self::$app_config, 'on_demand');
			}
			else
			{
				self::$app_config = $cache;
			}
		}
	}

	static function Core_get($key)
	{
		return self::get($key, 'core');
	}

	static function Core_getAll()
	{
		return self::getAll('core');
	}

	static function Core_listMatching($key)
	{
		return self::listMatching($key, 'core');
	}



	static function get($key, $context='app')
	{
		return isset(self::${$context.'_config'}[$key]) ? self::${$context.'_config'}[$key] : null;
	}

	static function getAll($context='app')
	{
		return self::${$context.'_config'};
	}

	static function listMatching($key, $context='app')
	{
		return array_filter(self::${$context.'_config'}, function($keys) use ($key)
		{

			return preg_match('/^'.$key.'.*$/', $keys);
		}, ARRAY_FILTER_USE_KEY);
	}
}