<?php

class Core
{
	static private $fc_instance = null;
	static public $root = '';

	static function init($dev=false)
	{
		self::$root = dirname(dirname(__DIR__));
		define('ROOT', self::$root);

		spl_autoload_register(function($class)
		{
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			require_once(ROOT.DIRECTORY_SEPARATOR.'private.'.DIRECTORY_SEPARATOR.$path . '.class.php');
		});
	}

	static function run()
	{
		//self::$fc_instance = new FrontController();
		//self::$fc_instance->routeTo($url);

		$t = new www\controllers\Test();
		$t->test();
	}
}