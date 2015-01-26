<?php

namespace core;

class Controller
{
	private $method_access = array();

	public function __construct()
	{
		if(false === $cache = \Core\Cache::get('access', get_class($this), 'on_demand'))
		{
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
			ob_start();
			$response = call_user_func_array(array($this, $method), $args);
			$buffer = ob_get_clean();

			var_dump($buffer);
		}
		else
		{
			exit('FORBIDDEN');
		}
	}
}