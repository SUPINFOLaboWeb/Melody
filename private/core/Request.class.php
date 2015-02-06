<?php

namespace core;

class Request
{
	private $session 			= null;
	private $attributes 		= array();
	private $args 				= array();
	private static $instance 	= null;

	private function __construct()
	{
		
	}

	public static function &getInstance($args=array())
	{
		if(is_null(self::$instance))
		{
			self::$instance = new Request();
			self::$instance->args = $args;

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

	public function getArg($name)
	{
		return (isset(self::getInstance()->args[$name]) ? self::getInstance()->args[$name] : null);
	}

	public function getArgs()
	{
		return self::getInstance()->args;
	}

	public function getParameter($name)
	{
		return self::getInstance()->getVars('_REQUEST', $name);
	}

	public function getParameters()
	{
		return self::getInstance()->getVars('_REQUEST');
	}

	public function getPostParameter($name)
	{
		return self::getInstance()->getVar('_POST', $name);
	}

	public function getPostParameters()
	{
		return self::getInstance()->getVars('_POST');
	}

	public function getGetParameter($name)
	{
		return self::getInstance()->getVar('_GET', $name);
	}

	public function getGetParameters()
	{
		return self::getInstance()->getVars('_GET');
	}

	public function getCookieParameter($name)
	{
		return self::getInstance()->getVar('_COOKIE', $name);
	}

	public function getCookieParameters()
	{
		return self::getInstance()->getVars('_COOKIE');
	}

	private function getVar($varname, $key)
	{
		return (isset($$varname[$key]) ? $$varname[$key] : null);
	}

	private function getVars($varname)
	{
		return $$varname;
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