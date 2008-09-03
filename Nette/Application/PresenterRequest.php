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
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/

require_once dirname(__FILE__) . '/../Object.php';



/**
 * Application presenter request. Immutable object.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @property mixed
 */
final class PresenterRequest extends /*Nette::*/Object
{
	// sources:
	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';
	const FORWARD = 'FORWARD';

	/** @var string */
	private $source;

	/** @var string */
	private $name;

	/** @var array */
	private $params;

	/** @var array */
	private $post;

	/** @var array */
	private $files;



	/**
	 * @param  string  fully qualified presenter name (module:module:presenter)
	 * @param  string  source
	 * @param  array   variables provided to the presenter usually via URL
	 * @param  array   variables provided to the presenter via POST
	 * @param  array   all uploaded files
	 */
	public function __construct($name, $source, array $params, array $post = array(), array $files = array())
	{
		$this->name = $name;
		$this->source = $source;
		$this->params = $params;
		$this->post = $post;
		$this->files = $files;
	}



	/**
	 * Retrieve the presenter name.
	 * @return string
	 */
	public function getPresenterName()
	{
		return $this->name;
	}



	/**
	 * Retrieve the source.
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}



	/**
	 * Returns all variables provided to the presenter (usually via URL).
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}



	/**
	 * Returns all variables provided to the presenter via POST.
	 * @return array
	 */
	public function getPost()
	{
		return $this->post;
	}



	/**
	 * Returns all uploaded files.
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}



	/**
	 * Is source HTTP POST?
	 * @return boolean
	 */
	public function isPost()
	{
		return $this->source === self::HTTP_POST;
	}



	/**
	 * Is source forward?
	 * @return boolean
	 */
	public function isForward()
	{
		return $this->source === self::FORWARD;
	}



	/**
	 * @internal
	 * @return array
	 */
	public function modify($var, $key, $value = NULL)
	{
		if (func_num_args() === 3) {
			$this->{$var}[$key] = $value;

		} else {
			$this->$var = $key;
		}
	}

}
