<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


/**
 * PHP 5.3 replacement for internal interface \SessionHandlerInterface
 * @see http://php.net/manual/en/class.sessionhandlerinterface.php
 *
 * @author     Matěj Koubík
 */
interface SessionHandlerInterface
{

	/**
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public function open($savePath, $sessionName);

	/**
	 * @return bool
	 */
	public function close();

	/**
	 * @param  string
	 * @return string
	 */
	public function read($id);

	/**
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public function write($id, $data);

	/**
	 * @param  string
	 * @return bool
	 */
	public function destroy($id);

	/**
	 * @param  string
	 * @return bool
	 */
	public function gc($maxlifetime);

}
