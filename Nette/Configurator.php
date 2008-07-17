<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/


/**
 * Nette::Environment helper.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
class Configurator
{
	/** @var bool */
	public $useCache;

	/** @var string */
	public $defaultConfigFile = '%appDir%/config.ini';



	/**
	 * Detect environment mode.
	 * @param  string mode name
	 * @return bool
	 */
	public function detectMode($name)
	{
		switch ($name) {
		case 'environment':
			// environment name autodetection
			if (defined('ENVIRONMENT')) {
				return ENVIRONMENT;

			} elseif ($this->detectMode('console')) {
				return Environment::CONSOLE;

			} else {
				return Environment::getMode('localhost') ? Environment::DEVELOPMENT : Environment::PRODUCTION;
			}

		case 'localhost':
			// detect by IP address
			if (isset($_SERVER['SERVER_ADDR'])) {
				$oct = explode('.', $_SERVER['SERVER_ADDR']);
				return (count($oct) === 4) && ($oct[0] === '10' || $oct[0] === '127' || ($oct[0] === '171' && $oct[1] > 15 && $oct[1] < 32)
					|| ($oct[0] === '169' && $oct[1] === '254') || ($oct[0] === '192' && $oct[1] === '168'));

			} else {
				return FALSE;
			}

		case 'debug':
			// determines if the debugger is active
			if (defined('DEBUG_MODE')) {
				return (bool) DEBUG_MODE;

			} else {
				return Environment::getMode('localhost') && isset($_REQUEST['DBGSESSID']);
				// function_exists('DebugBreak');
			}

		case 'console':
			return PHP_SAPI === 'cli';

		default:
			// unknowm mode
			return NULL;
		}
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|Config  file name or Config object
	 * @return Config
	 */
	public function loadConfig($file = NULL)
	{
		$name = Environment::getName();

		if ($this->useCache === NULL) {
			$this->useCache = $name === Environment::PRODUCTION;
		}

		$cache = $this->useCache ? Environment::getCache('Nette.Environment') : NULL;

		if (isset($cache[$name])) {
			Environment::swapState($cache[$name]);
			$config = Environment::getConfig();

		} else {
			if ($file instanceof Config) {
				$config = $file;
				$file = NULL;

			} else {
				if ($file === NULL) {
					$file = $this->defaultConfigFile;
				}
				$file = Environment::expand($file);
				$config = Config::fromFile($file, $name, 0);
			}

			// process environment variables
			if ($config->variable instanceof Config) {
				foreach ($config->variable as $key => $value) {
					Environment::setVariable($key, $value);
				}
			}

			if (isset($config->set->include_path)) {
				$config->set->include_path = strtr($config->set->include_path, ';', PATH_SEPARATOR);
			}

			$config->expand();
			$config->setReadOnly();

			// process services
			$locator = Environment::getServiceLocator();
			if ($config->service instanceof Config) {
				foreach ($config->service as $key => $value) {
					$locator->addService($value, $key);
				}
			}

			// save cache
			if ($cache) {
				$state = Environment::swapState(NULL);
				$state[0] = $config; // TODO: better!
				$cache->save($name, $state, array('files' => $file));
			}
		}


		// check temporary directory - TODO: discuss
		/*
		$dir = Environment::getVariable('tempDir');
		if ($dir && !(is_dir($dir) && is_writable($dir))) {
			trigger_error("Temporary directory '$dir' is not writable", E_USER_NOTICE);
		}
		*/

		// process ini settings
		if ($config->set instanceof Config) {
			if (!function_exists('ini_set')) {
				throw new /*::*/NotSupportedException('Function ini_set() is not enabled.');
			}

			foreach ($config->set as $key => $value) {
				ini_set($key, $value);
			}
		}

		// define constants
		if ($config->const instanceof Config) {
			foreach ($config->const as $key => $value) {
				define($key, $value);
			}
		}

		// set mode
		if (isset($config->mode)) {
			foreach(explode(',', $config->mode) as $mode) {
				Environment::setMode($mode);
			}
		}

		// execute services - TODO: discuss
		/*
		if ($config->run) {
			$run = (array) $config->run;
			ksort($run);
			foreach ($run as $value) {
				$a = strrpos($value, ':');
				$service = substr($value, 0, $a - 1);
				$service = $locator->getService($service);
				$method = substr($value, $a + 1);
				$service->$method();
			}
		}
		*/

		return $config;
	}

}
