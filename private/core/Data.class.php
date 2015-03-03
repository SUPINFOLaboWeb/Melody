<?php
namespace core;
use PDO;

class Data
{
	protected static $instance;
	protected static $included = array();

	private function __construct()
	{

	}

	private static function connection($settings)
	{
		$emulate_prepares_below_version = '5.1.17';

		$dsndefaults = array_fill_keys(array('host', 'port', 'unix_socket', 'dbname', 'charset'), null);
		$dsnarr = array_intersect_key($settings, $dsndefaults);
		$dsnarr += $dsndefaults;

		// connection options I like
		$options = array(
			PDO::ATTR_ERRMODE => (DEV_ENV) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		);

		// connection charset handling for old php versions
		if ($dsnarr['charset'] and version_compare(PHP_VERSION, '5.3.6', '<')) 
		{
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES '.$dsnarr['charset'];
		}
		$dsnpairs = array();
		foreach ($dsnarr as $k => $v) 
		{
			if ($v===null) 
				continue;
			$dsnpairs[] = "{$k}={$v}";
		}

		$dsn = 'mysql:'.implode(';', $dsnpairs);
		$dbh = new PDO($dsn, $settings['user'], $settings['pass'], $options);

		// Set prepared statement emulation depending on server version
		$serverversion = $dbh->getAttribute(PDO::ATTR_SERVER_VERSION);
		$emulate_prepares = (version_compare($serverversion, $emulate_prepares_below_version, '<'));
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulate_prepares);

		return $dbh;
	}

	static function getInstance($infos=array())
	{
		if(!isset(self::$instance))
		{
			if(empty($infos))
			{
				$settings = array(
								'host' 			=> \Core\Config::get('database_host'), 
								'port' 			=> \Core\Config::get('database_port'), 
								'unix_socket' 	=> \Core\Config::get('database_unix_socket'), 
								'dbname'	 	=> \Core\Config::get('database_dbname'), 
								'charset' 		=> \Core\Config::get('database_charset'), 
								'user' 			=> \Core\Config::get('database_user'), 
								'pass' 			=> \Core\Config::get('database_passwd')
							);

				self::$instance = self::connection($settings);
			}
			else
			{
								$settings = array(
								'host' 			=> $infos['host'], 
								'port' 			=> \Core\Config::get('database_port'), 
								'unix_socket' 	=> \Core\Config::get('database_unix_socket'), 
								'dbname'	 	=> $infos['dbnale'], 
								'charset' 		=> \Core\Config::get('database_charset'), 
								'user' 			=> $infos['user'], 
								'pass' 			=> $infos['pass']
							);
				self::$instance = self::connection($settings);
			}
		}
		return self::$instance;
	}

	static function __callStatic($method, $args)
	{
		return self::invoke('\www'.(empty(array_filter(FrontController::$apps)) ? '' : '\\'.join(FrontController::$apps)).'\models\\'.$method, $args);
	}

	static function invoke($class, $args=array())
	{

		return new $class($args);
	}
}