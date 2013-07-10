<?php

/**
 * Test: Nette\Http\Helpers.
 *
 * @author     David Grudl
 * @package    Nette\Http
 */

use Nette\Http\Helpers;


require __DIR__ . '/../bootstrap.php';


test(function() {
	Assert::true( Helpers::ipMatch('192.168.68.233', '192.168.68.233') );
	Assert::false( Helpers::ipMatch('192.168.68.234', '192.168.68.233') );
	Assert::true( Helpers::ipMatch('192.168.64.0', '192.168.68.233/12') );
	Assert::false( Helpers::ipMatch('192.168.63.255', '192.168.68.233/12') );
	Assert::true( Helpers::ipMatch('192.168.79.254', '192.168.68.233/12') );
	Assert::false( Helpers::ipMatch('192.168.80.0', '192.168.68.233/12') );
	Assert::true( Helpers::ipMatch('127.0.0.1', '192.168.68.233/32') );
	Assert::true( Helpers::ipMatch('127.0.0.1', '192.168.68.233/33') );
});
