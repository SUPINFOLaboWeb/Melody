<?php

namespace core;

class Resource
{
	static function getAbsWithParamEval($path, $vars=array())
	{
		ob_start();

		extract($vars);
		include(ROOT.DIRECTORY_SEPARATOR.ltrim($path, '/'));

		return ob_get_clean();
	}

	static function getWithParamEval($path, $vars)
	{
		return self::getAbsWithParamEval('private'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.ltrim($path, '/'), $vars);
	}

	static function getAbsEval($path)
	{
		return self::getAbsWithParamEval($path);
	}

	static function getEval($path)
	{
		return self::getAbsWithParamEval('private'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.ltrim($path, '/'));
	}

	static function getAbs($path)
	{
		return file_get_contents(ROOT.DIRECTORY_SEPARATOR.ltrim($path, '/'));	
	}

	static function get($path)
	{
		return self::getAbs('private'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.ltrim($path, '/'));
	}
}