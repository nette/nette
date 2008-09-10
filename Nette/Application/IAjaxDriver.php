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



/**
 * AJAX strategy interface.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 */
interface IAjaxDriver
{

	/**
	 * Generates link.
	 * @param  string
	 * @return string
	 */
	function link($url);

	/**
	 * @param  Nette::Web::IHttpResponse
	 * @return void
	 */
	function open(/*Nette::Web::*/IHttpResponse $httpResponse);

	/**
	 * @return void
	 */
	function close();

	/**
	 * Updates the snippet content.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	function updateSnippet($id, $content);

	/**
	 * Updates the presenter state.
	 * @param  array
	 * @return void
	 */
	function updateState($state);

	/**
	 * @param  string
	 * @return void
	 */
	function redirect($uri);

	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	function fireEvent($event, $arg);

}
