<?php
namespace Core;

class Core
{
	static public $root = '';

	static function init($dev=false)
	{
		self::$root = dirname(dirname(__DIR__));
		define('ROOT', self::$root);

		spl_autoload_register(function($class)
		{
			require_once(self::class2path($class));
		});

		if($dev)
		{
			\Core\Tools::rrmdir(ROOT.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'cache');
		}
		Config::Init();
	}

	static function class2path($class)
	{
		//var_dump($class);
		$old 	= $path 	= str_replace('\\', DIRECTORY_SEPARATOR, $class);
		$path 				= explode('\\', $class);
		$class 				= array_pop($path);
		$container 			= array_pop($path);
		$component 			= array_shift($path);

		switch($container)
		{

			case 'controllers':
				$class = str_replace('Controller', '', $class).'.class.php';
			break;
			default:
				$class = $class.'.class.php';
			break;
		}

		if($component == 'www' && count($path) > 0)
		{
			$path = array_map(function($e){ return 'apps'.DIRECTORY_SEPARATOR.$e; }, $path);
		}
		$path[] = '';
		

		return  ROOT
				.DIRECTORY_SEPARATOR.'private'
				.DIRECTORY_SEPARATOR.strtolower($component)
				.DIRECTORY_SEPARATOR.strtolower(join(DIRECTORY_SEPARATOR, $path)).strtolower($container)
				.DIRECTORY_SEPARATOR.ucfirst(strtolower($class));
	}

	static function run($uri)
	{
		FrontController::routeTo($uri);
	}
}