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
		self::$uri 			= $uri;

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
					self::throwError(404);
				}
			}
			else
			{
				if(Tools::method_exists($default_class, $default_method))
				{
					$class 	= $default_class;
					$method = $default_method;
				}
				else
				{
					if(Tools::method_exists($default_class, $default_method_alt))
					{
						$class = $default_class;
						$method = $default_method_alt;
					}
					else
					{
						self::throwError(404);
					}
				}
			}	
		}

		// la méthode existe bien sinon 404
		if(!Tools::method_exists($class, $method))
		{
			if(!Tools::method_exists($class, $method_alt))
			{
				if(file_exists(Core::class2path($class_alt)))
				{
					self::redirect(array_merge(array_filter($data['apps']), array($data['method'])), '', '', $data['args']);
				}
				else
				{
					if(Tools::method_exists($default_class, $default_method))
					{
						$class = $default_class;
						$method = $default_method;
					}
					else
					{
						if(Tools::method_exists($default_class, $default_method_alt))
						{
							$class = $default_class;
							$method = $default_method_alt;
						}
						else
						{
							self::throwError(404);
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

		self::$apps 			= $data['apps'];
		self::$class 			= $class;
		self::$method 			= $method;
		self::$request_method 	= strtolower($_SERVER['REQUEST_METHOD']);

							Config::loadFor($data['apps']);

		$page->{'__melody_invoke'}($method, array(\Core\Request::getInstance($data['args']), new \Core\Response()));

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
		else
		{
			self::throwError(403, 'URN not matching');
		}
	}	

	static function applyHostConfiguration($data)
	{
		$apps = $data['apps'];

		// recupération du chemin "complet"
		$de = explode('.', $_SERVER['HTTP_HOST']);
		$dn = implode('.', array_slice($de, 0, -2));
		$host = implode('.', $de);

		$configFromDomain = Config::Host_getConfigFromDomain($_SERVER['HTTP_HOST']);
		$apps = array_merge($configFromDomain, $apps);
		$configFromPath = Config::Host_getConfigFromPath(array_filter($apps));

		if(!empty($configFromPath))
		{	
			if($configFromPath['domain'] != $host)
			{
				switch($configFromPath['action'])
				{
					case 'forbid':
						self::throwError(403);
					break;
					case 'redirect':
						$host_apps = Config::Host_getConfigFromDomain($configFromPath['domain']);
						self::redirectURL(strtolower(explode('/', 	$_SERVER['SERVER_PROTOCOL'])[0]).'://'
																	.$configFromPath['domain']
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
		if(empty(Config::getAll()))
		{
			if(!is_null(self::$apps))
			{
				Config::loadFor(self::$apps);
			}
			else
			{
				Config::loadFor(array());
			}
		}

		$action = !is_null(Config::get('access_error_'.$code.'_action')) ?  Config::get('access_error_'.$code.'_action') : Config::get('access_error_default_action');
		$method_array = !is_null(Config::get('access_error_'.$code.'_controller')) ?  Config::get('access_error_'.$code.'_controller') : Config::get('access_error_default_controller');

		if(is_null($action) || is_null($method_array))
		{
			echo($code.' : '.$msg);
			exit();
		}
		else
		{
			list($controller, $method) = $method_array;
			switch($action)
			{
				case 'redirect':
					self::redirectController($controller, $method, Tools::base64url_encode(serialize(array(FrontController::$uri, $msg))));
				break;
				case 'include':
				default:
					echo $controller::invoke($method, Request::getInstance(), $msg);
				break;
			}
		}
		exit();
	}

	static function redirect($apps = array(), $controller='', $method='', $args=array())
	{
		self::redirectURL(Tools::urlfor($apps, $controller, $method, $args), true, true);
	}

	static function redirectController($class, $method, $args=array())
	{
		$class = (explode('\\', $class));
		$apps = array_slice($class, 2, -2);
		$controller = strtolower($class[count($class)-1]);

		self::redirect($apps, $controller, $method, $args);
	}

	static function redirectURL($url, $absolute=false, $external=false)
	{
		if(!$external)
		{
			($absolute) ? header('Location: /'.$url) : header('Location: '.Config::get('app_base_url').$url);
		}
		else
		{
			header('Location: '.$url);
		}
		exit();
	}
	
}