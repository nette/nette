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
 * Rendering helpers for HTTP.
 *
 * @author     David Grudl
 */
final class Helpers
{

	/**
	 * Is IPv4 address in CIDR block?
	 * @return bool
	 */
	public static function ipMatch($ip, $mask)
	{
		list($mask, $size) = explode('/', $mask . '/0');
		return !strncmp(sprintf('%032b', ip2long($ip)), sprintf('%032b', ip2long($mask)), max(0, 32 - $size));
	}

}
