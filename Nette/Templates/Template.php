<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Templates
 */

namespace Nette\Templates;

use Nette;



/**
 * Template stored in file.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Templates
 */
class Template extends BaseTemplate implements IFileTemplate
{
	/** @var int */
	public static $cacheExpire = FALSE;

	/** @var Nette\Caching\ICacheStorage */
	private static $cacheStorage;

	/** @var string */
	private $file;



	/**
	 * Constructor.
	 * @param  string  template file path
	 */
	public function __construct($file = NULL)
	{
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}



	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return Template  provides a fluent interface
	 */
	public function setFile($file)
	{
		if (!is_file($file)) {
			throw new \FileNotFoundException("Missing template file '$file'.");
		}
		$this->file = $file;
		return $this;
	}



	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	public function getFile()
	{
		return $this->file;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new \InvalidStateException("Template file name was not specified.");
		}

		$this->__set('template', $this);

		$cache = new Nette\Caching\Cache($this->getCacheStorage(), 'Nette.Template');
		$key = md5($this->file) . '.' . basename($this->file);
		$cached = $content = $cache[$key];

		if ($content === NULL) {
			if (!$this->getFilters()) {
				$this->onPrepareFilters($this);
			}

			if (!$this->getFilters()) {
				Nette\Loaders\LimitedScope::load($this->file, $this->getParams());
				return;
			}

			try {
				$shortName = $this->file;
				$shortName = str_replace(Nette\Environment::getVariable('appDir'), "\xE2\x80\xA6", $shortName);
			} catch (\Exception $foo) {
			}

			$content = $this->compile(file_get_contents($this->file), "file $shortName");
			$cache->save(
				$key,
				$content,
				array(
					Nette\Caching\Cache::FILES => $this->file,
					Nette\Caching\Cache::EXPIRE => self::$cacheExpire,
				)
			);
			$cache->release();
			$cached = $cache[$key];
		}

		if ($cached !== NULL && self::$cacheStorage instanceof TemplateCacheStorage) {
			Nette\Loaders\LimitedScope::load($cached['file'], $this->getParams());
			fclose($cached['handle']);

		} else {
			Nette\Loaders\LimitedScope::evaluate($content, $this->getParams());
		}
	}



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	public static function setCacheStorage(Nette\Caching\ICacheStorage $storage)
	{
		self::$cacheStorage = $storage;
	}



	/**
	 * @return Nette\Caching\ICacheStorage
	 */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			self::$cacheStorage = new TemplateCacheStorage(Nette\Environment::getVariable('tempDir'));
		}
		return self::$cacheStorage;
	}

}
