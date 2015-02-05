<?php

namespace core;

class FrontController
{
	static public $uri 				= null;
	static public $apps				= null;
	static public $class 			= null;
	static public $method 			= null;
	static public $request_method 	= null;


	static function routeTo($uri)
	{

		$data 				= self::parseURI($uri);

		$data['apps'] 		= self::applyHostConfiguration($data);
		

		$default_class		= 'www\\'.join('\\', $data['apps']).'controllers\\'.ucfirst(strtolower('home'));
		$class 				= 'www\\'.join('\\', $data['apps']).'controllers\\'.ucfirst(strtolower($data['controller']));
		$class_alt 			= 'www\\'.join('\\', $data['apps']).$data['method'].'\\controllers\\'.ucfirst(strtolower($data['controller']));
		$method 			= strtolower($_SERVER['REQUEST_METHOD']).str_replace('-', '', mb_convert_case($data['method'], MB_CASE_TITLE, "UTF-8")).'Action';
		$method_alt 		= 'any'.str_replace('-', '', mb_convert_case($data['method'], MB_CASE_TITLE, "UTF-8")).'Action';
		$default_method 	= strtolower($_SERVER['REQUEST_METHOD']).ucfirst(strtolower($data['controller'])).str_replace('-', '', mb_convert_case($data['method'], MB_CASE_TITLE, "UTF-8")).'Action';
		$default_method_alt = 'any'.ucfirst(strtolower($data['controller'])).str_replace('-', '', mb_convert_case($data['method'], MB_CASE_TITLE, "UTF-8")).'Action';




		// le controller existe bien sinon 404
		if(!file_exists(Core::class2path($class)))
		{
			if($data['controller'] == 'home' && $data['method'] != 'index') 
			{
				if(file_exists(Core::class2path($class_alt)))
				{
					self::redirect(array_merge(array_filter($data['apps']), array($data['method'])), '', '', $data['args']);
				}
				else
				{
					self::throwError(404, '/'.$uri);
				}
			}
			else
			{
				if(method_exists($default_class, $default_method))
				{
					$class 	= $default_class;
					$method = $default_method;
				}
				else
				{
					if(method_exists($default_class, $default_method_alt))
					{
						$class = $default_class;
						$method = $default_method_alt;
					}
					else
					{
						self::throwError(404, '/'.$uri);
					}
				}
			}	
		}

		// la méthode existe bien sinon 404
		if(!method_exists($class, $method))
		{
			if(!method_exists($class, $method_alt))
			{
				if(file_exists(Core::class2path($class_alt)))
				{
					self::redirect(array_merge(array_filter($data['apps']), array($data['method'])), '', '', $data['args']);
				}
				else
				{
					if(method_exists($default_class, $default_method))
					{
						$class = $default_class;
						$method = $default_method;
					}
					else
					{
						if(method_exists($default_class, $default_method_alt))
						{
							$class = $default_class;
							$method = $default_method_alt;
						}
						else
						{
							self::throwError(404, '/'.$uri);
						}
					}
				}
				
			}
			else
			{
				$method = $method_alt;
			}
		}

		$page = new $class();

		self::$uri 				= $uri;
		self::$apps 			= $data['apps'];
		self::$class 			= $class;
		self::$method 			= $method;
		self::$request_method 	= strtolower($_SERVER['REQUEST_METHOD']);

		Config::loadFor($data['apps']);

		$page->{'__melody_invoke'}($method, array(\Core\Request::getInstance(), new \Core\Response()));

	}

	static function parseURI($uri)
	{
		if(preg_match('/^((?:[a-z0-9]+\/)*)(?:([a-z0-9]+)(?:-([a-z0-9-]+)(?:\/(.*))?)?)?$/', $uri, $matches))
		{

			$data = array(
				'URI' 			=> $matches[0],
				'apps' 			=> (isset($matches[1]) ? explode('/', $matches[1]) : array()), 
				'controller' 	=> (isset($matches[2]) ? $matches[2] : ''), 
				'method' 		=> (isset($matches[3]) ? $matches[3] : ''), 
				'args' 			=> array_filter(explode('/', (isset($matches[4]) ? $matches[4] : ''))), 
			);

			// petit trick
			if(empty($data['method']))
			{
				$data['method'] = $data['controller'];
				$data['controller'] = '';
			}

			// remplacement pour les valeurs par défaut
			$data['controller'] = (!empty($data['controller']) ? $data['controller'] : 'home');
			$data['method'] 	= (!empty($data['method']) ? $data['method'] : 'index');

			return $data;
		}
	}	

	static function applyHostConfiguration($data)
	{
		$apps = $data['apps'];

		// recupération du chemin "complet"
		$de = explode('.', $_SERVER['HTTP_HOST']);
		$dn = implode('.', array_slice($de, 0, -2));
		$host = implode('.', array_slice($de, -2));


		$configFromDomain = Config::Host_getConfigFromDomain($dn);
		$apps = array_merge($configFromDomain, $apps);
		$configFromPath = Config::Host_getConfigFromPath(array_filter($apps));

		if(!empty($configFromPath))
		{
			if($configFromPath['domain'] != $dn)
			{
				switch($configFromPath['action'])
				{
					case 'forbid':
						self::throwError(403);
					break;
					case 'redirect':
						$host_apps = Config::Host_getConfigFromDomain($configFromPath['domain']);
						self::redirectURL(strtolower(explode('/', 	$_SERVER['SERVER_PROTOCOL'])[0]).'://'
																	.$configFromPath['domain'].'.'.$host
																	.'/'.Tools::urlfor(array_slice(array_filter($apps), count($host_apps)), 
																						$data['controller'] == 'home' ? '': $data['controller'], 
																						$data['method'] == 'index' ? '' : $data['method'], 
																						null, 
																	false), true, true); 
					break;
					case 'none':
					default:
						break;
				}
			}	
		}

		return $apps;
	}

	static function throwError($code, $msg='')
	{
		var_dump($code.' : '.$msg);
		exit();
	}

	static function redirect($apps = array(), $controller='', $method='', $args=array())
	{
		self::redirectURL(Tools::urlfor($apps, $controller, $method, $args), true, true);
	}

	static function redirectURL($url, $absolute=false, $external=false)
	{
		if(!$external)
		{
			($absolute) ? header('Location: /'.$url) : header('Location: '.\core\Config::get('app_base_url').$url);
		}
		else
		{
			header('Location: '.$url);
		}
		exit();
	}
	
}