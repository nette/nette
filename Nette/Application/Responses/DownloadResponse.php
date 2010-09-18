<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
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

	/** @var bool */
	private $resuming = TRUE;


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
	 * Sets download resuming on or off.
	 * @param  bool
	 * @return Nette\Application\DownloadResponse provides fluent interface
	 */
	public function setResuming($on)
	{
		$this->resuming = (bool) $on;
		return $this;
	}



	/**
	 * Is download resuming?
	 * @return bool
	 */
	public function isResuming()
	{
		return $this->resuming;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
		$response = Nette\Environment::getHttpResponse();
		$response->setContentType($this->contentType);
		$response->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');

		$filesize = $length = filesize($this->file);
		$resource = fopen($this->file, 'r');

		if ($this->resuming) {
			$response->setHeader('Accept-Ranges', 'bytes');

			$range = Nette\Environment::getHttpRequest()->getHeader('Range');
			if ($range !== NULL) {
				$range = substr($range, 6); // 6 == strlen('bytes=')
				list($start, $end) = explode('-', $range);
				if ($start == NULL) {
					$start = 0;
				}
				if ($end == NULL) {
					$end = $filesize - 1;
				}

				if ($start < 0 || $end <= $start || $end > $filesize -1) {
					$response->setCode(416); // requested range not satisfiable
					return;
				}

				$response->setCode(206);
				$response->setHeader('Content-Range', 'bytes ' . $start . '-' . $end . '/' . $filesize);
				$length = $end - $start + 1;
				
				fseek($resource, $start);
			} else {
				$response->setHeader('Content-Range', 'bytes 0-' . ($filesize - 1) . '/' . $filesize);
			}
		}

		$response->setHeader('Content-Length', $length);

		while (!feof($resource)) {
			echo fread($resource, 4*1024*1024); // 4 MB step
		}
		fclose($f);
	}

}
