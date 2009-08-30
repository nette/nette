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
 * @package    Nette\Application
 */

/*namespace Nette\Application;*/



require_once dirname(__FILE__) . '/../../Object.php';

require_once dirname(__FILE__) . '/../../Application/IPresenterResponse.php';



/**
 * JSON response used for AJAX requests.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Application
 */
class JsonResponse extends /*Nette\*/Object implements IPresenterResponse
{
	/** @var stdClass */
	private $payload;

	/** @var string */
	private $contentType;



	/**
	 * @param  stdClass|array  payload
	 * @param  string    MIME content type
	 */
	public function __construct($payload, $contentType = NULL)
	{
		$this->payload = (object) $payload;
		$this->contentType = $contentType ? $contentType : 'application/json';
	}



	/**
	 * @return stdClass
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
		/*Nette\*/Environment::getHttpResponse()->setContentType($this->contentType);
		/*Nette\*/Environment::getHttpResponse()->expire(FALSE);
		echo json_encode($this->payload);
	}

}
