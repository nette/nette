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
 * @package    Nette::Web
 */

/*namespace Nette::Web;*/


require_once dirname(__FILE__) . '/../Object.php';



/**
 * Provides access to individual files that have been uploaded by a client.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Web
 * @version    $Revision$ $Date$
 */
class HttpUploadedFile extends /*Nette::*/Object
{
	/* @var array */
	private $name;

	/* @var array */
	private $type;

	/* @var array */
	private $size;

	/* @var array */
	private $tmpName;

	/* @var int */
	private $error;



	public function __construct($value)
	{
		$this->name = isset($value['name']) ? $value['name'] : NULL;
		$this->type = isset($value['type']) ? $value['type'] : NULL;
		$this->size = isset($value['size']) ? $value['size'] : NULL;
		$this->tmpName = isset($value['tmp_name']) ? $value['tmp_name'] : NULL;
		$this->error = isset($value['error']) ? $value['error'] : UPLOAD_ERR_NO_FILE;
	}



	/**
	 * Returns the file name.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * Returns the MIME content type of an uploaded file.
	 * @return string
	 */
	public function getContentType()
	{
		return $this->type;
	}



	/**
	 * Returns the size of an uploaded file.
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}



	/**
	 * Returns the path to an uploaded file.
	 * @return string
	 */
	public function getTemporaryFile()
	{
		return $this->tmpName;
	}



	/**
	 * Returns the error code.
	 * @return int
	 */
	public function getError()
	{
		return $this->error;
	}



	/**
	 * Is there any error?
	 * @return bool
	 */
	public function isOK()
	{
		return $this->error === UPLOAD_ERR_OK;
	}



	/**
	 * Move uploaded file to new location.
	 * @param  string
	 * @return void
	 */
	public function move($dest)
	{
		return move_uploaded_file($this->tmpName, $dest);
	}



	/**
	 * Returns the dimensions of an uploaded image as array.
	 * @return array
	 */
	public function getImageSize()
	{
		return $this->error === UPLOAD_ERR_OK ? getimagesize($this->tmpName) : NULL;
	}

}
