<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Http;

use Nette;


/**
 * Adapter from Nette\Http\ISessionStorage to native SessionHandlerInterface.
 * The only difference is method names.
 *
 * @author     Matěj Koubík
 */
final class SessionStorageAdapter extends Nette\Object implements \SessionHandlerInterface
{
	private $sessionStorage;


	public function __construct(ISessionStorage $sessionStorage)
	{
		$this->sessionStorage = $sessionStorage;
	}


	/**
	 * @inheritDoc
	 */
	public function open($savePath, $sessionName)
	{
		return $this->sessionStorage->open($savePath, $sessionName);
	}


	/**
	 * @inheritDoc
	 */
	public function close()
	{
		return $this->sessionStorage->close();
	}


	/**
	 * @inheritDoc
	 */
	public function read($id)
	{
		return $this->sessionStorage->read($id);
	}


	/**
	 * @inheritDoc
	 */
	public function write($id, $data)
	{
		return $this->sessionStorage->write($id, $data);
	}


	/**
	 * @inheritDoc
	 */
	public function destroy($id)
	{
		return $this->sessionStorage->remove($id);
	}


	/**
	 * @inheritDoc
	 */
	public function gc($maxlifetime)
	{
		return $this->sessionStorage->clean($maxlifetime);
	}

}
