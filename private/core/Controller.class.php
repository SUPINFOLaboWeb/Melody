<?php

namespace core;

class Controller
{
	private $method_access = array();

	public function __construct()
	{
		if(false === $cache = \Core\Cache::get('access', get_class($this)))
		{
			if(method_exists($this, '__melody_access'))
				$this->__melody_access();

			\Core\Cache::create('access', get_class($this), $this->method_access, 'on_demand');
		}
		else
		{
			$this->method_access = $cache;
		}
	}

	public function setDefaultAccessRole($role)
	{
		foreach(get_class_methods($this) as $method)
		{
			$this->method_access[$method] = $role;
		}
	}

	public function setAccessRoleException($method, $role)
	{
		$this->method_access[$method] = $role;
	}

	public function __melody_invoke($method, $args)
	{
		$role 			= (isset($_SESSION[\Core\Config::Core_get('access_role_session_key')]) ? $_SESSION[\Core\Config::Core_get('access_role_session_key')] : \Core\Config::Core_get('access_role_if_missing'));
		$access_role 	= (isset($this->method_access[$method]) ? $this->method_access[$method] : (!is_null(\Core\Config::get('access_role')) ? Core\Config::get('access_role') : \Core\Config::Core_get('access_role_default')));

		if($role >= $access_role)
		{
			$class = get_class($this);
			if(false === $cache = \Core\Cache::get('controller', $class.'_'.$method))
			{
				ob_start();
				$__melody_response = call_user_func_array(array($this, $method), $args);
				$buffer = ob_get_clean();

				echo self::execute_view($__melody_response, $buffer, $class);
				
			}
			else
			{
				echo $cache;
			}
		}
		else
		{
			FrontController::throwError(403);
		}
	}

	public static function invoke($method, $req, ...$args)
	{
		$class = get_called_class();
		
		if(false === $cache = \Core\Cache::get('controller', $class.'_'.$method))
		{
			ob_start();
			$c = new $class();

			array_unshift($args, new Response());
			array_unshift($args, $req);

			$__melody_response = call_user_func_array(array($c, $method), $args);
			$buffer = ob_get_clean();

			return self::execute_view($__melody_response, $buffer, $class);
		}
		else
		{
			return $cache;
		}
	}

	private static function execute_view($__melody_response = null, $buffer, $class)
	{
		$__melody_response = (is_null($__melody_response) ? new \core\Response() : $__melody_response);

		// permet d'accéder aux différentes variables directement dans la view
		$vars = $__melody_response->viewvars;

		if(!empty($__melody_response->viewpath))
		{
			if($__melody_response->viewpath[1])
			{
				list($apps, $file) = $__melody_response->viewpath[0];
				$path = Tools::pathfor($apps, 'views'.DIRECTORY_SEPARATOR.$file, '.php');

			}					
			else
			{
				$path = Tools::pathfor(array_filter(!is_null(FrontController::$apps) ? FrontController::$apps : array()), 'views'.DIRECTORY_SEPARATOR.$__melody_response->viewpath[0], '.php');
			}

			ob_start();
			include($path);
			$output = ob_get_clean();

			
			if($__melody_response->cache)
			{
				\Core\Cache::create('controller', $class.'_'.$method, $output, $__melody_response->cache_mode, $__melody_response->expiration);
			}

			return  $output;

		}

		return $buffer;
	}
}