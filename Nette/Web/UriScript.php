<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Web
 */

namespace Nette\Web;

use Nette;



/**
 * Extended HTTP URL.
 *
 * <pre>
 *                    basePath   relativeUri
 *                       |           |
 *                    /-----\/------------------\
 * http://nette.org/admin/script.php/pathinfo/?name=param#fragment
 *                    \_______________/\________/
 *                           |              |
 *                      scriptPath       pathInfo
 * </pre>
 *
 * - basePath:    /admin/ (everything before relative URI not including the script name)
 * - baseUri:     http://nette.org/admin/
 * - scriptPath:  /admin/script.php
 * - relativeUri: script.php/pathinfo/
 * - pathInfo:    /pathinfo/ (additional path information)
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Web
 *
 * @property   string $scriptPath
 * @property-read string $basePath
 * @property-read string $baseUri
 * @property-read string $relativeUri
 * @property-read string $pathInfo
 */
class UriScript extends Uri
{
	/** @var string */
	private $scriptPath = '';



	/**
	 * Sets the script-path part of URI.
	 * @param  string
	 * @return UriScript  provides a fluent interface
	 */
	public function setScriptPath($value)
	{
		$this->updating();
		$this->scriptPath = (string) $value;
		return $this;
	}



	/**
	 * Returns the script-path part of URI.
	 * @return string
	 */
	public function getScriptPath()
	{
		return $this->scriptPath;
	}



	/**
	 * Returns the base-path.
	 * @return string
	 */
	public function getBasePath()
	{
		return (string) substr($this->scriptPath, 0, strrpos($this->scriptPath, '/') + 1);
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
