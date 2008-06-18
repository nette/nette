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
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/

/*use Nette::Collections::Hashtable;*/

require_once dirname(__FILE__) . '/../Object.php';



/**
 * Application presenter request. Immutable object.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 * @property mixed
 */
final class PresenterRequest extends /*Nette::*/Object
{
	// sources:
	const HTTP_GET = 'get';
	const HTTP_POST = 'post';
	const HTTP_AJAX = 'ajax';
	const FORWARD = 'forward';

	/** @var string */
	private $source;

	/** @var string */
	private $presenter;

	/** @var array */
	private $params;

	/** @var Nette::Collections::Hashtable|NULL */
	private $post;

	/** @var Nette::Collections::Hashtable|NULL */
	private $files;



	/**
	 * @param  string  fully qualified presenter name (module:module:presenter)
	 * @param  string  source
	 * @param  array   variables provided to the presenter usually via URL
	 * @param  array   variables provided to the presenter via POST
	 * @param  array   all uploaded files
	 */
	public function __construct($name, $source, array $params, Hashtable $post = NULL, Hashtable $files = NULL)
	{
		$this->setPresenterName($name);
		$this->source = $source;
		//$this->params = $params;
		$this->params = new Hashtable($params);
		$this->post = $post;
		$this->files = $files;
	}



	/**
	 * Retrieve the presenter name.
	 * @param  string
	 * @return void
	 */
	public function setPresenterName($name)
	{
		if (!is_string($name) || $name === '') {
			throw new /*::*/InvalidArgumentException("Presenter name must be non-empty string.");
		}

		$this->presenter = $name;
	}



	/**
	 * Retrieve the presenter name.
	 * @return string
	 */
	public function getPresenterName()
	{
		return $this->presenter;
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
	 * @return Nette::Collections::Hashtable
	 */
	public function getParams()
	{
		return $this->params;
	}



	/**
	 * Returns all variables provided to the presenter via POST.
	 * @return Nette::Collections::Hashtable|NULL
	 */
	public function getPost()
	{
		return $this->post;
	}



	/**
	 * Returns all uploaded files.
	 * @return Nette::Collections::Hashtable|NULL
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
	 */
	public function setReadOnly()
	{
		$this->params->setReadOnly();

		if ($this->post) {
			$this->post->setReadOnly();
		}

		if ($this->files) {
			$this->files->setReadOnly();
		}
	}

}
