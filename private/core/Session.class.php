<?php

namespace core;

class Session
{
	public function &__get($name)
	{
		return $_SESSION[\Core\Config::Core_get('session_content_varname')][$name];
	}

	public function __set($name, $value)
	{
		return $_SESSION[\Core\Config::Core_get('session_content_varname')][$name] = $value;
	}

	public function __isset($name)
	{
		return isset($_SESSION[\Core\Config::Core_get('session_content_varname')][$name]);
	}

	public function __unset($name)
	{
		unset($_SESSION[\Core\Config::Core_get('session_content_varname')][$name]);
	}
}