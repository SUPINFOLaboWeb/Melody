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

		Config::Init();
	}

	static function class2path($class)
	{
		$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		$path = explode('\\', $class);
		$class = array_pop($path);

		return ROOT.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.strtolower(join(DIRECTORY_SEPARATOR, $path)).DIRECTORY_SEPARATOR.ucfirst(strtolower($class)).'.class.php';
	}

	static function run($uri)
	{
		FrontController::routeTo($uri);
	}
}