<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Web;

use Nette;



/**
 * HTTP-specific tasks.
 *
 * @author     David Grudl
 */
class HttpContext extends Nette\Object
{

	/** @var IHttpRequest */
	private $request;
	
	/** @var IHttpResponse */
	private $response;
	
	
	/**
	 * @param IHttpRequest
	 * @param IHttpResponse	 
	 */
	public function __construct(IHttpRequest $request, IHttpResponse $response)
	{
		$this->request = $request;
		$this->response = $response;
	}



	/**
	 * Attempts to cache the sent entity by its last modification date
	 * @param  string|int|DateTime  last modified time
	 * @param  string  strong entity tag validator
	 * @return bool
	 */
	public function isModified($lastModified = NULL, $etag = NULL)
	{
		$response = $this->response;
		$request = $this->request;

		if ($lastModified) {
			$response->setHeader('Last-Modified', $response->date($lastModified));
		}
		if ($etag) {
			$response->setHeader('ETag', '"' . addslashes($etag) . '"');
		}

		$ifNoneMatch = $request->getHeader('If-None-Match');
		if ($ifNoneMatch === '*') {
			$match = TRUE; // match, check if-modified-since

		} elseif ($ifNoneMatch !== NULL) {
			$etag = $response->getHeader('ETag');

			if ($etag == NULL || strpos(' ' . strtr($ifNoneMatch, ",\t", '  '), ' ' . $etag) === FALSE) {
				return TRUE;

			} else {
				$match = TRUE; // match, check if-modified-since
			}
		}

		$ifModifiedSince = $request->getHeader('If-Modified-Since');
		if ($ifModifiedSince !== NULL) {
			$lastModified = $response->getHeader('Last-Modified');
			if ($lastModified != NULL && strtotime($lastModified) <= strtotime($ifModifiedSince)) {
				$match = TRUE;

			} else {
				return TRUE;
			}
		}

		if (empty($match)) {
			return TRUE;
		}

		$response->setCode(IHttpResponse::S304_NOT_MODIFIED);
		return FALSE;
	}

}
