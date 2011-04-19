<?php

/**
 * Test: Nette\Http\Url malformed URI.
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Url;



require __DIR__ . '/../bootstrap.php';



try {
	$url = new Url(':');

	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Malformed or unsupported URI ':'.", $e );
}
