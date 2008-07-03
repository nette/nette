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
 * AJAX strategy interface.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
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
	 * @return void
	 */
	function open();

	/**
	 * @return void
	 */
	function close();

	/**
	 * Updates the partial content.
	 * @param  string
	 * @param  string
	 * @return void
	 */
	function addPartial($id, $content);

	/**
	 * Updates the presenter state.
	 * @param  array
	 * @return void
	 */
	function setState($state);

	/**
	 * @param  string
	 * @return void
	 */
	function redirect($uri);

	/**
	 * @param  string
	 * @return void
	 */
	function error($message);

}
