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
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Provides access to individual files that have been uploaded by a client.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
 */
class HttpUploadedFile extends /*Nette\*/Object
{
	/* @var string */
	private $name;

	/* @var string */
	private $type;

	/* @var string */
	private $realType;

	/* @var string */
	private $size;

	/* @var string */
	private $tmpName;

	/* @var int */
	private $error;



	public function __construct($value)
	{
		foreach (array('name', 'type', 'size', 'tmp_name', 'error') as $key) {
			if (!isset($value[$key]) || !is_scalar($value[$key])) {
				$this->error = UPLOAD_ERR_NO_FILE;
				return; // or throw exception?
			}
		}
		//if (!is_uploaded_file($value['tmp_name'])) {
			//throw new /*\*/InvalidStateException("Filename '$value[tmp_name]' is not a valid uploaded file.");
		//}
		$this->name = $value['name'];
		$this->type = $value['type'];
		$this->size = $value['size'];
		$this->tmpName = $value['tmp_name'];
		$this->error = $value['error'];
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
		if ($this->isOk() && $this->realType === NULL) {
			if (extension_loaded('fileinfo')) {
				$this->realType = finfo_file(finfo_open(FILEINFO_MIME), $this->tmpName);

			} elseif (function_exists('mime_content_type')) {
				$this->realType = mime_content_type($this->tmpName);

			} else {
				$info = getImageSize($this->tmpName);
				$this->realType = isset($info['mime']) ? $info['mime'] : $this->type;
			}
		}
		return $this->realType;
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
	 * Returns the image.
	 * @return Nette\Image
	 */
	public function getImage()
	{
		return /*Nette\*/Image::fromFile($this->tmpName);
	}



	/**
	 * Returns the path to an uploaded file.
	 * @return string
	 */
	public function __toString()
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
	 * @return bool
	 */
	public function move($dest)
	{
		if (move_uploaded_file($this->tmpName, $dest)) {
			$this->tmpName = $dest;
			return TRUE;

		} else {
			return FALSE;
		}
	}



	/**
	 * Returns the dimensions of an uploaded image as array.
	 * @return array
	 */
	public function getImageSize()
	{
		return $this->isOk() ? getimagesize($this->tmpName) : NULL;
	}

}
