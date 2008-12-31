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

/*use Nette\Caching\Cache, Nette\Environment;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Caching template helper.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
class CachingHelper extends /*Nette\*/Object
{
	/** @var array */
	private $frame;

	/** @var string */
	private $key;



	/**
	 * Starts the output cache. Returns CachingHelper object if buffering was started.
	 * @param  string
	 * @param  string
	 * @param  array
	 * @return CachingHelper
	 */
	public static function create($key, $file, $tags)
	{
		$cache = self::getCache();
		if (isset($cache[$key])) {
			echo $cache[$key];
			return FALSE;

		} else {
			$obj = new self;
			$obj->key = $key;
			$obj->frame = array(
				Cache::FILES => array($file),
				Cache::TAGS => $tags,
				Cache::EXPIRE => rand(86400 * 4, 86400 * 7),
			);
			ob_start();
			return $obj;
		}
	}



	/**
	 * Stops and saves the cache.
	 * @return void
	 */
	public function save()
	{
		$this->getCache()->save($this->key, ob_get_flush(), $this->frame);
		$this->key = $this->frame = NULL;
	}



	/**
	 * Adds the file dependency.
	 * @param  string
	 * @return void
	 */
	public function addFile($file)
	{
		$this->frame[Cache::FILES][] = $file;
	}



	/**
	 * Adds the cached item dependency.
	 * @param  string
	 * @return void
	 */
	public function addItem($item)
	{
		$this->frame[Cache::ITEMS][] = $item;
	}



	/********************* backend ****************d*g**/



	/**
	 * @return Nette\Caching\Cache
	 */
	protected static function getCache()
	{
		return /*Nette\*/Environment::getCache('Nette.Template.Curly');
	}

}