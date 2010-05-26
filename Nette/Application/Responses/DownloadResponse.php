<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */

namespace Nette\Application;

use Nette;



/**
 * File download response.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class DownloadResponse extends Nette\Object implements IPresenterResponse
{
	/** @var string */
	private $file;

	/** @var string */
	private $contentType;

	/** @var string */
	private $name;



	/**
	 * @param  string  file path
	 * @param  string  user name name
	 * @param  string  MIME content type
	 */
	public function __construct($file, $name = NULL, $contentType = NULL)
	{
		if (!is_file($file)) {
			throw new BadRequestException("File '$file' doesn't exist.");
		}

		$this->file = $file;
		$this->name = $name ? $name : basename($file);
		$this->contentType = $contentType ? $contentType : 'application/octet-stream';
	}



	/**
	 * Returns the path to a downloaded file.
	 * @return string
	 */
	final public function getFile()
	{
		return $this->file;
	}



	/**
	 * Returns the file name.
	 * @return string
	 */
	final public function getName()
	{
		return $this->name;
	}



	/**
	 * Returns the MIME content type of a downloaded file.
	 * @return string
	 */
	final public function getContentType()
	{
		return $this->contentType;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
		Nette\Environment::getHttpResponse()->setContentType($this->contentType);
		Nette\Environment::getHttpResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
		readfile($this->file);
	}

}
