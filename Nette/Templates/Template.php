<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../Templates/BaseTemplate.php';

require_once dirname(__FILE__) . '/../Templates/IFileTemplate.php';



/**
 * Template stored in file.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
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
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return void
	 */
	public function setFile($file)
	{
		$this->file = $file;
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
			throw new /*\*/InvalidStateException("Template file name was not specified.");

		} elseif (!is_file($this->file) || !is_readable($this->file)) {
			throw new /*\*/FileNotFoundException("Missing template file '$this->file'.");
		}

		$this->__set('template', $this);

		$cache = new /*Nette\Caching\*/Cache($this->getCacheStorage(), 'Nette.Template');
		$key = md5($this->file) . '.' . basename($this->file);
		$cached = $content = $cache[$key];

		if ($content === NULL) {
			if (!$this->getFilters()) {
				$this->onPrepareFilters($this);
			}

			if (!$this->getFilters()) {
				/*Nette\Loaders\*/LimitedScope::load($this->file, $this->getParams());
				return;
			}

			try {
				$shortName = $this->file;
				$shortName = str_replace(/*Nette\*/Environment::getVariable('templatesDir'), "\xE2\x80\xA6", $shortName);
			} catch (Exception $foo) {
			}

			$content = $this->compile(file_get_contents($this->file), "file $shortName");
			$cache->save(
				$key,
				$content,
				array(
					/*Nette\Caching\*/Cache::FILES => $this->file,
					/*Nette\Caching\*/Cache::EXPIRE => self::$cacheExpire,
				)
			);
			$cached = $cache[$key];
		}

		if ($cached !== NULL && self::$cacheStorage instanceof TemplateCacheStorage) {
			/*Nette\Loaders\*/LimitedScope::load($cached['file'], $this->getParams());
			fclose($cached['handle']);

		} else {
			/*Nette\Loaders\*/LimitedScope::evaluate($content, $this->getParams());
		}
	}



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  Nette\Caching\Cache
	 * @return void
	 */
	public static function setCacheStorage(/*Nette\Caching\*/ICacheStorage $storage)
	{
		self::$cacheStorage = $storage;
	}



	/**
	 * @return Nette\Caching\ICacheStorage
	 */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			self::$cacheStorage = new TemplateCacheStorage(/*Nette\*/Environment::getVariable('cacheBase'));
		}
		return self::$cacheStorage;
	}

}
