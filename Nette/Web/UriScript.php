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
 * @package    Nette::Web
 * @version    $Id$
 */

/*namespace Nette::Web;*/


require_once dirname(__FILE__) . '/../Object.php';



/**
 * Extended HTTP URL
 *
 * http://nettephp.com/basePath/script.php/pathinfo/?name=param#fragment
 *                    \__________________/\________/
 *                              |              |
 *                            path          pathinfo
 *
 * basePath:    /basePath/ (everything before relative URI not including the script name)
 * baseUri:     http://nettephp.com/basePath/
 * scriptPath:  /basePath/script.php  (URI-path of the request with the script name)
 * relativeUri: script.php
 * pathInfo:    /pathinfo/ (additional path information)
 *
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 */
class UriScript extends Uri
{
	/** @var string */
	public $scriptPath;



	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		return substr($this->scriptPath, 0, strrpos($this->scriptPath, '/') + 1);
	}



	/**
	 * Returns the base-URI.
	 * @return string
	 */
	public function getBaseUri()
	{
		return $this->scheme . '://' . $this->getAuthority() . $this->getBasePath();
	}



	/**
	 * Returns the relative-URI.
	 * @return string
	 */
	public function getRelativeUri()
	{
		return (string) substr($this->path, strrpos($this->scriptPath, '/') + 1);
	}



	/**
	 * Returns the additional path information.
	 * @return string
	 */
	public function getPathInfo()
	{
		return (string) substr($this->path, strlen($this->scriptPath));
	}

}
