<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * Rendering presenter response.
 *
 * @author     David Grudl
 */
class RenderResponse extends Nette\Object implements IPresenterResponse
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
	final public function getSource()
	{
		return $this->source;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
		if ($this->source instanceof Nette\Templates\ITemplate) {
			$this->source->render();

		} else {
			echo $this->source;
		}
	}

}
