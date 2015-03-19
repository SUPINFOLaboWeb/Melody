<?php
namespace Core;

class Core
{
	static public $root = '';

	static function init($dev=false)
	{
		session_start();

		self::$root = dirname(dirname(__DIR__));
		define('ROOT', self::$root);
		define('DEV_ENV', $dev);
		define('PROD_ENV', !$dev);

		spl_autoload_register(function($class) use ($dev)
		{
			if(!class_exists($class, false))
			{
				if($dev)
				{
					try 
					{
						$path = self::class2path($class);
						if (!file_exists($path))
						{
							throw new \Exception ($path);
						}
						else
						{
							require_once($path); 
						}
					}
					catch(\Exception $e)
					 {    
						echo "Message : " . $e->getMessage()."\n";
						echo "Code : " . $e->getCode();
					}
				}
				else
				{
					include(self::class2path($class));
				}
			}
			else
			{
				var_dump('kamui');
			}

		});

		if($dev)
		{
			@\Core\Tools::rrmdir(ROOT.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'cache');
		}

		include(__DIR__.DIRECTORY_SEPARATOR.'alias.php');
		Config::Init();
	}

	static function class2path($class)
	{
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
			case 'lib':
				$container .= DIRECTORY_SEPARATOR.$class;
				$class = $class.'.class.php';
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
				.(!empty($component) ? DIRECTORY_SEPARATOR.$component : '')
				.DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, $path).$container
				.DIRECTORY_SEPARATOR.$class;
	}

	static function run($uri)
	{
		FrontController::routeTo($uri);
	}
}