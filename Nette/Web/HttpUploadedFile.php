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
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Provides access to individual files that have been uploaded by a client.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
 *
 * @property-read string $name
 * @property-read string $contentType
 * @property-read int $size
 * @property-read string $temporaryFile
 * @property-read Nette\Image $image
 * @property-read int $error
 * @property-read array $imageSize
 * @property-read bool $ok
 */
class HttpUploadedFile extends /*Nette\*/Object
{
	/* @var string */
	private $name;

	/* @var string */
	private $type;

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
		if ($this->isOk() && $this->type === NULL) {
			$info = getimagesize($this->tmpName);
			if (isset($info['mime'])) {
				$this->type = $info['mime'];

			} elseif (extension_loaded('fileinfo')) {
				$this->type = finfo_file(finfo_open(FILEINFO_MIME), $this->tmpName);

			} elseif (function_exists('mime_content_type')) {
				$this->type = mime_content_type($this->tmpName);
			}

			if (!$this->type) {
				$this->type = 'application/octet-stream';
			}
		}
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
	public function isOk()
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
		@unlink($dest); // needed in PHP < 5.3 & Windows; intentionally @
		if (rename($this->tmpName, $dest)) {
			$this->tmpName = $dest;
			return TRUE;

		} else {
			return FALSE;
		}
	}



	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 * @return bool
	 */
	public function isImage()
	{
		return in_array($this->getContentType(), array('image/gif', 'image/png', 'image/jpeg'), TRUE);
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
	 * Returns the dimensions of an uploaded image as array.
	 * @return array
	 */
	public function getImageSize()
	{
		return $this->isOk() ? getimagesize($this->tmpName) : NULL;
	}

}
