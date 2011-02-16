<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * File download response.
 *
 * @author     David Grudl
 */
class DownloadResponse extends Nette\Object implements IPresenterResponse
{
	/** @var string */
	private $file;

	/** @var string */
	private $contentType;

	/** @var string */
	private $name;

	/** @var Nette\Web\IHttpResponse */
	private $httpResponse;



	/**
	 * @param  Nette\Web\IHttpResponse  http response
	 * @param  string  file path
	 * @param  string  user name name
	 * @param  string  MIME content type
	 */
	public function __construct(Nette\Web\IHttpResponse $httpResponse, $file, $name = NULL, $contentType = NULL)
	{
		$this->httpResponse = $httpResponse;

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
		$this->httpResponse->setContentType($this->contentType);
		$this->httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
		readfile($this->file);
	}

}
