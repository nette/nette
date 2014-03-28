<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application\Responses;

use Nette;


/**
 * String output response.
 *
 * @author     David Grudl
 *
 * @property-read mixed $source
 */
class TextResponse extends Nette\Object implements Nette\Application\IResponse
{
	/** @var mixed */
	private $source;


	/**
	 * @param  mixed  renderable variable
	 */
	public function __construct($source)
	{
		$this->source = $source;
	}


	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}


	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		if ($this->source instanceof Nette\Application\UI\ITemplate) {
			$this->source->render();

		} else {
			echo $this->source;
		}
	}

}
