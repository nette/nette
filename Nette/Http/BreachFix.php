<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Http;

use Nette;


/**
 * BREACH attack fix
 *
 * @author     David Grudl
 */
class BreachFix extends Nette\Object
{
	/** @var resource */
	public $hash;


	public static function enable()
	{
		if (empty($_SERVER['HTTPS']) || !strcasecmp($_SERVER['HTTPS'], 'off')) {
			return;
		}

		$me = new static;
		$me->hash = hash_init('crc32b');
		ob_start(function($s) use ($me) {
			hash_update($me->hash, $s);
			return $s;
		}, 4096);
	}


	public function __destruct()
	{
		if (preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()))) {
			return;
		}

		$hash = hash_final($this->hash);
		//header('X-Breach-Spoil: ' . str_repeat('.', $i % 64));
		
		for ($i = hexdec($hash[0]); $i; $i--) {
			echo strtr(base_convert($hash, 16, 4), '0123', " \t\r\n");
			$hash = hash('crc32b', $hash);
		}
	}

}
