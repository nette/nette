<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Loaders
 */

namespace Nette\Loaders;

use Nette,
	Nette\String;



/**
 * Nette auto loader is responsible for loading classes and interfaces.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Loaders
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
	private $rebuilded = FALSE;

	/** @var string */
	private $acceptMask;

	/** @var string */
	private $ignoreMask;



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
		if (isset($this->list[$type])) {
			if ($this->list[$type] !== FALSE) {
				LimitedScope::load($this->list[$type][0]);
				self::$count++;
			}

		} else {
			$this->list[$type] = FALSE;

			if ($this->autoRebuild === NULL) {
				$this->autoRebuild = !$this->isProduction();
			}

			if ($this->autoRebuild) {
				if ($this->rebuilded) {
					$this->getCache()->save($this->getKey(), $this->list);
				} else {
					$this->rebuild();
				}
			}

			if ($this->list[$type] !== FALSE) {
				LimitedScope::load($this->list[$type][0]);
				self::$count++;
			}
		}
	}



	/**
	 * Rebuilds class list cache.
	 * @return void
	 */
	public function rebuild()
	{
		$this->getCache()->save($this->getKey(), callback($this, '_rebuildCallback'));
		$this->rebuilded = TRUE;
	}



	/**
	 * @internal
	 */
	public function _rebuildCallback()
	{
		$this->acceptMask = self::wildcards2re($this->acceptFiles);
		$this->ignoreMask = self::wildcards2re($this->ignoreDirs);
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
			if ($pair) $res[$class] = $pair[0];
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
		$class = strtolower($class);
		if (!empty($this->list[$class]) && $this->list[$class][0] !== $file) {
			spl_autoload_call($class); // hack: enables exceptions
			throw new \InvalidStateException("Ambiguous class '$class' resolution; defined in $file and in " . $this->list[$class][0] . ".");
		}
		$this->list[$class] = array($file, $time);
	}



	/**
	 * Scan a directory for PHP files, subdirectories and 'netterobots.txt' file.
	 * @param  string
	 * @return void
	 */
	private function scanDirectory($dir)
	{
		if (is_file($dir)) {
			if (!isset($this->files[$dir]) || $this->files[$dir] !== filemtime($dir)) {
				$this->scanScript($dir);
			}
			return;
		}

		$iterator = dir($dir);
		if (!$iterator) return;

		$disallow = array();
		if (is_file($dir . '/netterobots.txt')) {
			foreach (file($dir . '/netterobots.txt') as $s) {
				if ($matches = String::match($s, '#^disallow\\s*:\\s*(\\S+)#i')) {
					$disallow[trim($matches[1], '/')] = TRUE;
				}
			}
			if (isset($disallow[''])) return;
		}

		while (FALSE !== ($entry = $iterator->read())) {
			if ($entry == '.' || $entry == '..' || isset($disallow[$entry])) continue;

			$path = $dir . DIRECTORY_SEPARATOR . $entry;

			// process subdirectories
			if (is_dir($path)) {
				// check ignore mask
				if (!String::match($entry, $this->ignoreMask)) {
					$this->scanDirectory($path);
				}
				continue;
			}

			if (is_file($path) && String::match($entry, $this->acceptMask)) {
				if (!isset($this->files[$path]) || $this->files[$path] !== filemtime($path)) {
					$this->scanScript($path);
				}
			}
		}

		$iterator->close();
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
		$level = 0;
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
					if ($level === 0) {
						$this->addClass($namespace . $name, $file, $time);
					}
					break;

				case $T_NAMESPACE:
					$namespace = $name . '\\';
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



	/**
	 * Converts comma separated wildcards to regular expression.
	 * @param  string
	 * @return string
	 */
	private static function wildcards2re($wildcards)
	{
		$mask = array();
		foreach (explode(',', $wildcards) as $wildcard) {
			$wildcard = trim($wildcard);
			$wildcard = addcslashes($wildcard, '.\\+[^]$(){}=!><|:#');
			$wildcard = strtr($wildcard, array('*' => '.*', '?' => '.'));
			$mask[] = $wildcard;
		}
		return '#^(' . implode('|', $mask) . ')$#i';
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
