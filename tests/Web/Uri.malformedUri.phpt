<?php

/**
 * Test: Nette\Web\Uri malformed URI.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

use Nette\Web\Uri;



require __DIR__ . '/../initialize.php';



try {
	$uri = new Uri(':');

	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Malformed or unsupported URI ':'.", $e );
}
