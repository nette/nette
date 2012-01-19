<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * JSON response used for AJAX requests.
 *
 * @author     David Grudl
 */
class JsonResponse extends Nette\Object implements IPresenterResponse
{
	/** @var array|stdClass */
	private $payload;

	/** @var string */
	private $contentType;



	/**
	 * @param  array|stdClass  payload
	 * @param  string    MIME content type
	 */
	public function __construct($payload, $contentType = NULL)
	{
		if (!is_array($payload) && !($payload instanceof \stdClass)) {
			throw new \InvalidArgumentException("Payload must be array or anonymous class, " . gettype($payload) . " given.");
		}
		$this->payload = $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}



	/**
	 * @return array|stdClass
	 */
	final public function getPayload()
	{
		return $this->payload;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
		Nette\Environment::getHttpResponse()->setContentType($this->contentType);
		Nette\Environment::getHttpResponse()->setExpiration(FALSE);
		echo json_encode($this->payload);
	}

}
