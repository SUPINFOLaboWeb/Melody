<?php

namespace core;

class Request
{
	private $session 			= null;
	private $attributes 		= array();
	private static $instance 	= null;

	private function __construct()
	{
		
	}

	public static function &getInstance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new Request();

			if(!isset($_SESSION[\Core\Config::Core_get('flash_varname')]))
			{
				$_SESSION[\Core\Config::Core_get('flash_varname')] = array('new' => array(), 'old' => array());
			}
			else
			{
				$_SESSION[\Core\Config::Core_get('flash_varname')]['old'] = $_SESSION[\Core\Config::Core_get('flash_varname')]['new'];
				$_SESSION[\Core\Config::Core_get('flash_varname')]['new'] = array();
			}
		}

		return self::$instance;
	}

	public function getSession()
	{
		if(is_null(self::getInstance()->session))
			return self::getInstance()->session = new \Core\Session();
		else
			return self::getInstance()->$session;
	}

	public function getParameters($name)
	{
		return $_REQUEST[$name];
	}

	public function getPostParameters($name)
	{
		return $_POST[$name];
	}

	public function getGetParameters($name)
	{
		return $_GET[$name];
	}

	public function getCookieParameters($name)
	{
		return $_COOKIE[$name];
	}

	public function setFlash($name, $value, $type)
	{
		if(!is_null($name) && !empty($name))
			return $_SESSION[\Core\Config::Core_get('session_flash_varname')]['new'][$name] = array($value, $type);
		else
			return $_SESSION[\Core\Config::Core_get('session_flash_varname')]['new'][] = array($value, $type);
	}

	public function getFlash($name)
	{
		return $_SESSION[\Core\Config::Core_get('session_flash_varname')]['old'][$name];
	}

	public function getAllFlash()
	{
		return $_SESSION[\Core\Config::Core_get('session_flash_varname')]['old'];
	}

	public function getFlashByType($type)
	{
		return array_filter($_SESSION[\Core\Config::Core_get('session_flash_varname')]['old'], function($e) use ($type){ return $e['type'] === $type; });
	}

	public function setAttribute($name, $value)
	{
		return self::getInstance()->attributes[$name] = $value;
	}

	public function &getAttribute($name)
	{
		return self::getInstance()->attributes[$name];
	}
}