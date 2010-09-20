<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Loaders;

use Nette,
	Nette\String;



/**
 * Nette auto loader is responsible for loading classes and interfaces.
 *
 * @author     David Grudl
 */
class RobotLoader extends AutoLoader
{
	/** @var array */
	public $scanDirs;

	/** @var string  comma separated wildcards */
	public $ignoreDirs = '.*, *.old, *.bak, *.tmp, temp';

	/** @var string  comma separated wildcards */
	public $acceptFiles = '*.php, *.php5';

	/** @var bool */
	public $autoRebuild;

	/** @var array */
	private $list = array();

	/** @var array */
	private $files;

	/** @var bool */
	private $rebuilt = FALSE;



	/**
	 */
	public function __construct()
	{
		if (!extension_loaded('tokenizer')) {
			throw new \Exception("PHP extension Tokenizer is not loaded.");
		}
	}



	/**
	 * Register autoloader.
	 * @return void
	 */
	public function register()
	{
		$cache = $this->getCache();
		$key = $this->getKey();
		if (isset($cache[$key])) {
			$this->list = $cache[$key];
		} else {
			$this->rebuild();
		}

		if (isset($this->list[strtolower(__CLASS__)]) && class_exists('Nette\Loaders\NetteLoader', FALSE)) {
			NetteLoader::getInstance()->unregister();
		}

		parent::register();
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\'); // PHP namespace bug #49143

		if (!isset($this->list[$type]) || ($this->list[$type] !== FALSE && !is_file($this->list[$type][0]))) {
			$this->list[$type] = FALSE;

			if ($this->autoRebuild === NULL) {
				$this->autoRebuild = !$this->isProduction();
			}

			if ($this->autoRebuild) {
				if ($this->rebuilt) {
					$this->getCache()->save($this->getKey(), $this->list);
				} else {
					$this->rebuild();
				}
			}

			if ($this->list[$type] !== FALSE) {
				LimitedScope::load($this->list[$type][0]);
				self::$count++;
			}

		} elseif ($this->list[$type] !== FALSE) {
			LimitedScope::load($this->list[$type][0]);
			self::$count++;
		}
	}



	/**
	 * Rebuilds class list cache.
	 * @return void
	 */
	public function rebuild()
	{
		$this->getCache()->save($this->getKey(), callback($this, '_rebuildCallback'));
		$this->rebuilt = TRUE;
	}



	/**
	 * @internal
	 */
	public function _rebuildCallback()
	{
		foreach ($this->list as $pair) {
			if ($pair) $this->files[$pair[0]] = $pair[1];
		}
		foreach (array_unique($this->scanDirs) as $dir) {
			$this->scanDirectory($dir);
		}
		$this->files = NULL;
		return $this->list;
	}



	/**
	 * @return array of class => filename
	 */
	public function getIndexedClasses()
	{
		$res = array();
		foreach ($this->list as $class => $pair) {
			if ($pair) $res[$pair[2]] = $pair[0];
		}
		return $res;
	}



	/**
	 * Add directory (or directories) to list.
	 * @param  string|array
	 * @return void
	 * @throws \DirectoryNotFoundException if path is not found
	 */
	public function addDirectory($path)
	{
		foreach ((array) $path as $val) {
			$real = realpath($val);
			if ($real === FALSE) {
				throw new \DirectoryNotFoundException("Directory '$val' not found.");
			}
			$this->scanDirs[] = $real;
		}
	}



	/**
	 * Add class and file name to the list.
	 * @param  string
	 * @param  string
	 * @param  int
	 * @return void
	 */
	private function addClass($class, $file, $time)
	{
		$lClass = strtolower($class);
		if (!empty($this->list[$lClass]) && $this->list[$lClass][0] !== $file && is_file($this->list[$lClass][0])) {
			$e = new \InvalidStateException("Ambiguous class '$class' resolution; defined in $file and in " . $this->list[$lClass][0] . ".");
			if (PHP_VERSION_ID >= 50300) {
				throw $e;
			} else { // hack
				Nette\Debug::_exceptionHandler($e);
				exit;
			}
		}
		$this->list[$lClass] = array($file, $time, $class);
	}



	/**
	 * Scan a directory for PHP files, subdirectories and 'netterobots.txt' file.
	 * @param  string
	 * @return void
	 */
	private function scanDirectory($dir)
	{
		if (is_dir($dir)) {
			$disallow = array();
			$iterator = Nette\Finder::findFiles(String::split($this->acceptFiles, '#[,\s]+#'))
				->filter(function($file) use (&$disallow){
					return !isset($disallow[$file->getPathname()]);
				})
				->from($dir)
				->exclude(String::split($this->ignoreDirs, '#[,\s]+#'))
				->filter($filter = function($dir) use (&$disallow){
					$path = $dir->getPathname();
					if (is_file("$path/netterobots.txt")) {
						foreach (file("$path/netterobots.txt") as $s) {
							if ($matches = String::match($s, '#^disallow\\s*:\\s*(\\S+)#i')) {
								$disallow[$path . str_replace('/', DIRECTORY_SEPARATOR, rtrim('/' . ltrim($matches[1], '/'), '/'))] = TRUE;
							}
						}
					}
					return !isset($disallow[$path]);
				});
			$filter(new \SplFileInfo($dir));
		} else {
			$iterator = new \ArrayIterator(array(new \SplFileInfo($dir)));
		}

		foreach ($iterator as $entry) {
			$path = $entry->getPathname();
			if (!isset($this->files[$path]) || $this->files[$path] !== $entry->getMTime()) {
				$this->scanScript($path);
			}
		}
	}



	/**
	 * Analyse PHP file.
	 * @param  string
	 * @return void
	 */
	private function scanScript($file)
	{
		$T_NAMESPACE = PHP_VERSION_ID < 50300 ? -1 : T_NAMESPACE;
		$T_NS_SEPARATOR = PHP_VERSION_ID < 50300 ? -1 : T_NS_SEPARATOR;

		$expected = FALSE;
		$namespace = '';
		$level = $minLevel = 0;
		$time = filemtime($file);
		$s = file_get_contents($file);

		if ($matches = String::match($s, '#//nette'.'loader=(\S*)#')) {
			foreach (explode(',', $matches[1]) as $name) {
				$this->addClass($name, $file, $time);
			}
			return;
		}

		foreach (token_get_all($s) as $token)
		{
			if (is_array($token)) {
				switch ($token[0]) {
				case T_COMMENT:
				case T_DOC_COMMENT:
				case T_WHITESPACE:
					continue 2;

				case $T_NS_SEPARATOR:
				case T_STRING:
					if ($expected) {
						$name .= $token[1];
					}
					continue 2;

				case $T_NAMESPACE:
				case T_CLASS:
				case T_INTERFACE:
					$expected = $token[0];
					$name = '';
					continue 2;
				case T_CURLY_OPEN:
				case T_DOLLAR_OPEN_CURLY_BRACES:
					$level++;
				}
			}

			if ($expected) {
				switch ($expected) {
				case T_CLASS:
				case T_INTERFACE:
					if ($level === $minLevel) {
						$this->addClass($namespace . $name, $file, $time);
					}
					break;

				case $T_NAMESPACE:
					$namespace = $name ? $name . '\\' : '';
					$minLevel = $token === '{' ? 1 : 0;
				}

				$expected = NULL;
			}

			if ($token === '{') {
				$level++;
			} elseif ($token === '}') {
				$level--;
			}
		}
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Caching\Cache
	 */
	protected function getCache()
	{
		return Nette\Environment::getCache('Nette.RobotLoader');
	}



	/**
	 * @return string
	 */
	protected function getKey()
	{
		return md5("v2|$this->ignoreDirs|$this->acceptFiles|" . implode('|', $this->scanDirs));
	}



	/**
	 * @return bool
	 */
	protected function isProduction()
	{
		return Nette\Environment::isProduction();
	}

}
