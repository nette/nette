<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * Rendering helpers for HTTP.
 *
 * @author     David Grudl
 */
class Helpers
{

	/**
	 * Is IP address in CIDR block?
	 * @return bool
	 */
	public static function ipMatch($ip, $mask)
	{
		list($mask, $size) = explode('/', $mask . '/');
		$ipv4 = strpos($ip, '.');
		$max = $ipv4 ? 32 : 128;
		if (($ipv4 xor strpos($mask, '.')) || $size < 0 || $size > $max) {
			return FALSE;
		} elseif ($ipv4) {
			$arr = array(ip2long($ip), ip2long($mask));
		} else {
			$arr = unpack('N*', inet_pton($ip) . inet_pton($mask));
			$size = $size === '' ? 0 : $max - $size;
		}
		$bits = implode('', array_map(function ($n) {
				return sprintf('%032b', $n);
		}, $arr));
		return substr($bits, 0, $max - $size) === substr($bits, $max, $max - $size);
	}

}
