<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Loaders;

use Nette,
	Nette\Utils\Strings,
	Nette\Caching\Cache;



/**
 * Nette auto loader is responsible for loading classes and interfaces.
 *
 * @author     David Grudl
 *
 * @property-read array $indexedClasses
 * @property   Nette\Caching\IStorage $cacheStorage
 */
class RobotLoader extends AutoLoader
{
	const RETRY_LIMIT = 3;

	/** @var array */
	public $scanDirs = array();

	/** @var string|array  comma separated wildcards */
	public $ignoreDirs = '.*, *.old, *.bak, *.tmp, temp';

	/** @var string|array  comma separated wildcards */
	public $acceptFiles = '*.php, *.php5';

	/** @var bool */
	public $autoRebuild = TRUE;

	/** @var array of lowered-class => [file, mtime, class] or num-of-retry */
	private $classes = array();

	/** @var array of file => [mtime, classes] */
	private $knownFiles;

	/** @var bool */
	private $rebuilt = FALSE;

	/** @var array of missing classes in this request */
	private $missing = array();

	/** @var Nette\Caching\IStorage */
	private $cacheStorage;



	/**
	 */
	public function __construct()
	{
		if (!extension_loaded('tokenizer')) {
			throw new Nette\NotSupportedException("PHP extension Tokenizer is not loaded.");
		}
	}



	/**
	 * Register autoloader.
	 * @param  bool  prepend autoloader?
	 * @return RobotLoader  provides a fluent interface
	 */
	public function register(/**/$prepend = FALSE/**/)
	{
		$this->classes = $this->getCache()->load($this->getKey(), new Nette\Callback($this, '_rebuildCallback'));
		parent::register(/**/$prepend/**/);
		return $this;
	}



	/**
	 * Handles autoloading of classes, interfaces or traits.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\'); // PHP namespace bug #49143
		$info = & $this->classes[$type];

		if ($this->autoRebuild && empty($this->missing[$type])
			&& (is_array($info) ? !is_file($info['file']) : $info < self::RETRY_LIMIT)
		) {
			$info = is_int($info) ? $info + 1 : 0;
			$this->missing[$type] = TRUE;
			if ($this->rebuilt) {
				$this->getCache()->save($this->getKey(), $this->classes);
			} else {
				$this->rebuild();
			}
			$info = & $this->classes[$type];
		}

		if (isset($info['file'])) {
			Nette\Utils\LimitedScope::load($info['file'], TRUE);

			if ($this->autoRebuild && !class_exists($type, FALSE) && !interface_exists($type, FALSE)
				&& (PHP_VERSION_ID < 50400 || !trait_exists($type, FALSE))
			) {
				$info = 0;
				$this->missing[$type] = TRUE;
				if ($this->rebuilt) {
					$this->getCache()->save($this->getKey(), $this->classes);
				} else {
					$this->rebuild();
				}
			}
			self::$count++;
		}
	}



	/**
	 * Rebuilds class list cache.
	 * @return void
	 */
	public function rebuild()
	{
		$this->getCache()->save($this->getKey(), new Nette\Callback($this, '_rebuildCallback'));
		$this->rebuilt = TRUE;
	}



	/**
	 * @internal
	 */
	public function _rebuildCallback()
	{
		$this->knownFiles = $missing = array();
		foreach ($this->classes as $class => $info) {
			if (is_array($info)) {
				$this->knownFiles[$info['file']]['time'] = $info['time'];
				$this->knownFiles[$info['file']]['classes'][] = $info['orig'];
			} else {
				$missing[$class] = $info;
			}
		}

		$this->classes = array();
		foreach (array_unique($this->scanDirs) as $dir) {
			foreach ($this->createFileIterator($dir) as $file) {
				$this->updateFile($file->getPathname());
			}
		}
		$this->classes += $missing;
		$this->knownFiles = NULL;
		return $this->classes;
	}



	/**
	 * @return array of class => filename
	 */
	public function getIndexedClasses()
	{
		$res = array();
		foreach ($this->classes as $class => $info) {
			if (is_array($info)) {
				$res[$info['orig']] = $info['file'];
			}
		}
		return $res;
	}



	/**
	 * Add directory (or directories) to list.
	 * @param  string|array
	 * @return RobotLoader  provides a fluent interface
	 * @throws Nette\DirectoryNotFoundException if path is not found
	 */
	public function addDirectory($path)
	{
		foreach ((array) $path as $val) {
			$real = realpath($val);
			if ($real === FALSE) {
				throw new Nette\DirectoryNotFoundException("Directory '$val' not found.");
			}
			$this->scanDirs[] = $real;
		}
		return $this;
	}



	/**
	 * Creates an iterator scaning directory for PHP files, subdirectories and 'netterobots.txt' files.
	 * @return \Iterator
	 */
	private function createFileIterator($dir)
	{
		if (!is_dir($dir)) {
			return new \ArrayIterator(array(new \SplFileInfo($dir)));
		}

		$ignoreDirs = is_array($this->ignoreDirs) ? $this->ignoreDirs : Strings::split($this->ignoreDirs, '#[,\s]+#');
		$disallow = array();
		foreach ($ignoreDirs as $item) {
			if ($item = realpath($item)) {
				$disallow[$item] = TRUE;
			}
		}

		$iterator = Nette\Utils\Finder::findFiles(is_array($this->acceptFiles) ? $this->acceptFiles : Strings::split($this->acceptFiles, '#[,\s]+#'))
			->filter(function($file) use (&$disallow){
				return !isset($disallow[$file->getPathname()]);
			})
			->from($dir)
			->exclude($ignoreDirs)
			->filter($filter = function($dir) use (&$disallow){
				$path = $dir->getPathname();
				if (is_file("$path/netterobots.txt")) {
					foreach (file("$path/netterobots.txt") as $s) {
						if ($matches = Strings::match($s, '#^(?:disallow\\s*:)?\\s*(\\S+)#i')) {
							$disallow[$path . str_replace('/', DIRECTORY_SEPARATOR, rtrim('/' . ltrim($matches[1], '/'), '/'))] = TRUE;
						}
					}
				}
				return !isset($disallow[$path]);
			});

		$filter(new \SplFileInfo($dir));
		return $iterator;
	}



	/**
	 * @return void
	 */
	private function updateFile($file)
	{
		if (isset($this->knownFiles[$file]) && $this->knownFiles[$file]['time'] === filemtime($file)) {
			$classes = $this->knownFiles[$file]['classes'];
		} else {
			$classes = $this->scanPhp(file_get_contents($file));
		}

		foreach ($classes as $class) {
			$lower = strtolower($class);
			if (isset($this->classes[$lower])) {
				/*5.2*if (PHP_VERSION_ID < 50300) {
					trigger_error("Ambiguous class $class resolution; defined in {$this->classes[$lower]['file']} and in $file.", E_USER_ERROR);
					exit;
				}*/
				throw new Nette\InvalidStateException("Ambiguous class $class resolution; defined in {$this->classes[$lower]['file']} and in $file.");
			}
			$this->classes[$lower] = array('file' => $file, 'time' => filemtime($file), 'orig' => $class);
		}
	}



	/**
	 * Searches classes, interfaces and traits in PHP file.
	 * @param  string
	 * @return array
	 */
	private function scanPhp($code)
	{
		$T_NAMESPACE = PHP_VERSION_ID < 50300 ? -1 : T_NAMESPACE;
		$T_NS_SEPARATOR = PHP_VERSION_ID < 50300 ? -1 : T_NS_SEPARATOR;
		$T_TRAIT = PHP_VERSION_ID < 50400 ? -1 : T_TRAIT;

		$expected = FALSE;
		$namespace = '';
		$level = $minLevel = 0;
		$classes = array();

		if ($matches = Strings::match($code, '#//nette'.'loader=(\S*)#')) {
			foreach (explode(',', $matches[1]) as $name) {
				$classes[] = $name;
			}
			return $classes;
		}

		foreach (@token_get_all($code) as $token) { // intentionally @
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
				case $T_TRAIT:
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
				case $T_TRAIT:
					if ($level === $minLevel) {
						$classes[] = $namespace . $name;
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
		return $classes;
	}



	/********************* backend ****************d*g**/



	/**
	 * @return RobotLoader
	 */
	public function setCacheStorage(Nette\Caching\IStorage $storage)
	{
		$this->cacheStorage = $storage;
		return $this;
	}



	/**
	 * @return Nette\Caching\IStorage
	 */
	public function getCacheStorage()
	{
		return $this->cacheStorage;
	}



	/**
	 * @return Nette\Caching\Cache
	 */
	protected function getCache()
	{
		if (!$this->cacheStorage) {
			trigger_error('Missing cache storage.', E_USER_WARNING);
			$this->cacheStorage = new Nette\Caching\Storages\DevNullStorage;
		}
		return new Cache($this->cacheStorage, 'Nette.RobotLoader');
	}



	/**
	 * @return string
	 */
	protected function getKey()
	{
		return array($this->ignoreDirs, $this->acceptFiles, $this->scanDirs);
	}

}
