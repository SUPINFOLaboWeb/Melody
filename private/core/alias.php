<?php
namespace 
{
	function urlfor($apps=array(), $controller='', $method='', $args=array())
	{
		return call_user_func_array(array('\Core\Tools', __FUNCTION__), func_get_args());
	}
}
