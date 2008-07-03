<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/



/**
 * AJAX output strategy.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
class AjaxDriver extends /*Nette::*/Object implements IAjaxDriver
{
	/** @var array */
	private $partials = array();



	/**
	 * Generates link.
	 * @param  string
	 * @return string
	 */
	public function link($url)
	{
		return "return !nette.action(" . ($url === NULL ? "this.href" : json_encode($url)) . ", this)";
	}



	/********************* partial rendering ****************d*g**/



	/**
	 * @return void
	 */
	public function open()
	{
		$httpResponse = /*Nette::*/Environment::getHttpResponse();
		$httpResponse->setHeader('Content-type: application/x-javascript; charset=utf-8', TRUE);
		$httpResponse->expire(FALSE);
	}



	/**
	 * @return void
	 */
	public function close()
	{
		foreach ($this->partials as $id => $content) {
			echo "nette.updateHtml(", json_encode($id), ", ", json_encode($content), ");\n";
		}
		$this->partials = array();
	}



	/**
	 * Updates the partial content.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function addPartial($id, $content)
	{
		$this->partials[$id] = $content;
	}



	/**
	 * Updates the presenter state.
	 * @param  array
	 * @return void
	 */
	public function setState($state)
	{
		$state = http_build_query($state, NULL, '&');
		echo "nette.updateState(", json_encode($state), ");\n";
	}



	/**
	 * @param  string
	 * @return void
	 */
	public function redirect($uri)
	{
		echo "nette.redirect(", json_encode($uri), ");\n";
	}



	/**
	 * @param  string
	 * @return void
	 */
	public function error($message)
	{
		echo "nette.error(", json_encode($message), ");\n";
	}

}
