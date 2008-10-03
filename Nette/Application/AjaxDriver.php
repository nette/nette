<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Application
 * @version    $Id$
 */

/*namespace Nette::Application;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * AJAX output strategy.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
class AjaxDriver extends /*Nette::*/Object implements IAjaxDriver
{
	/** @var array */
	private $json;



	/**
	 * Generates link.
	 * @param  string
	 * @return string
	 */
	public function link($url)
	{
		return "return !nette.action(" . ($url === NULL ? "this.href" : json_encode($url)) . ", this)";
	}



	/**
	 * @param  Nette::Web::IHttpResponse
	 * @return void
	 */
	public function open(/*Nette::Web::*/IHttpResponse $httpResponse)
	{
		$httpResponse->setContentType('application/x-javascript', 'utf-8');
		$httpResponse->expire(FALSE);
		$this->json = array(
			'nette' => array(),
		);
	}



	/**
	 * @return void
	 */
	public function close()
	{
		if ($this->json) {
			echo json_encode($this->json);
			$this->json = NULL;
		}
	}



	/**
	 * Updates the snippet content.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	public function updateSnippet($id, $content)
	{
		if ($this->json) {
			$this->json['snippets'][$id] = $content;
		}
	}



	/**
	 * Updates the presenter state.
	 * @param  array
	 * @return void
	 */
	public function updateState($state)
	{
		if ($this->json) {
			$this->json['state'] = $state;
		}
	}



	/**
	 * @param  string
	 * @return void
	 */
	public function redirect($uri)
	{
		if ($this->json) {
			$this->json['redirect'] = $uri;
		}
	}



	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	public function fireEvent($event, $arg)
	{
		if ($this->json) {
			$args = func_get_args();
			array_shift($args);
			$this->json['events'][] = array('event' => $event, 'args' => $args);
		}
	}

}
