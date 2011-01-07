<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Templates;

use Nette;



/**
 * Template cache storage.
 *
 * @author     David Grudl
 */
class TemplateCacheStorage extends Nette\Caching\FileStorage
{
	/** @var string */
	public $hint;


	/**
	 * Reads cache data from disk.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta[self::FILE],
			'handle' => $meta[self::HANDLE],
		);
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		$key = substr_replace($key, trim(strtr($this->hint, '\\/@', '.._'), '.') . '-', strpos($key, Nette\Caching\Cache::NAMESPACE_SEPARATOR) + 1, 0);
		return parent::getCacheFile($key) . '.php';
	}

}
