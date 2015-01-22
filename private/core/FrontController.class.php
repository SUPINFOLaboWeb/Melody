<?php

namespace core;

class FrontController
{
	static function routeTo($uri)
	{
		$data 		= self::parseURI($uri);
		$class 		= 'www\\'.join('\\', $data['apps']).'controllers\\'.$data['controller'];
		$class_alt 	= 'www\\'.join('\\', $data['apps']).$data['method'].'\\controllers\\'.$data['controller'];

		// le controller existe bien sinon 404
		if(!file_exists(\Core\Core::class2path($class)))
		{

			if($data['controller'] == 'default' && $data['method'] != 'index') 
			{
				if(!file_exists(\Core\Core::class2path($class_alt)))
				{
					self::throw_error(404, $uri);
				}
				else
				{
					$class = $class_alt;
				}
			}
		}

		$page = new $class();

		$method 	= strtolower($_SERVER['REQUEST_METHOD']).ucfirst(strtolower($data['method'])).'Action';
		$method_alt = 'any'.ucfirst(strtolower($data['method'])).'Action';

		// la méthode existe bien sinon 404
		if(!method_exists($page, $method))
		{
			if(!method_exists($page, $method_alt))
			{
				self::throw_error(404, $uri);
			}
			else
			{
				$method = $method_alt;
			}
		}

		$page->{$method}();
	}

	static function parseURI($uri)
	{
		if(preg_match('/^((?:[a-z0-9]+\/)*)(?:([a-z0-9]+)(?:-([a-z0-9-]+)(?:\/(.*))?)?)?$/', $uri, $matches))
		{

			$data = array(
				'URI' 			=>$matches[0],
				'apps' 			=>(isset($matches[1]) ? explode('/', $matches[1]) : array()), 
				'controller' 	=>(isset($matches[2]) ? $matches[2] : ''), 
				'method' 		=>(isset($matches[3]) ? $matches[3] : ''), 
				'args' 			=>(isset($matches[4]) ? $matches[4] : ''), 
			);

			// petit trick
			if(empty($data['method']))
			{
				$data['method'] = $data['controller'];
				$data['controller'] = '';
			}

			// remplacement pour les valeurs par défaut
			$data['controller'] = (!empty($data['controller']) ? $data['controller'] : 'default');
			$data['method'] 	= (!empty($data['method']) ? $data['method'] : 'index');

			return $data;
		}
	}

	static function throw_error($code, $arg)
	{
		var_dump($code.' : '.$msg);
	}
	
}