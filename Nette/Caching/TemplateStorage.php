<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Caching
 */

/*namespace Nette::Caching;*/



require_once dirname(__FILE__) . '/../Caching/FileStorage.php';



/**
 * Cache file storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Caching
 * @version    $Revision$ $Date$
 */
class TemplateStorage extends FileStorage
{

	/**
	 * Reads cache data from disk.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta['file'],
			'handle' => $meta['handle'],
		);
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getDataFile($key)
	{
		$path = $this->base . urlencode($key);
		if (substr($path, -5) !== '.phtml') $path .= '.phtml';
		return $path;
	}

}
