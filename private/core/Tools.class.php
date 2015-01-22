<?php

namespace core;

class Tools
{
	static function flatten($array, $prefix = '')
	{
		if(is_array($array))
		{
			$result = array();

			foreach ($array as $key => $value)
			{
				$new_key = $prefix . (empty($prefix) ? '' : '_') . $key;

				if (is_array($value) && Tools::is_assoc($value))
				{
					$result = array_merge($result, self::flatten($value, $new_key));
				}
				else
				{
					$result[$new_key] = $value;
				}
			}

			return $result;
		}
		else
		{
			return $array;
		}
	}

	static function is_assoc($array)
	{
		return (array_values($array) !== $array);
	}
}