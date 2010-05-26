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



require __DIR__ . '/../NetteTest/initialize.php';



try {
	$uri = new Uri(':');

} catch (Exception $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
Exception InvalidArgumentException: Malformed or unsupported URI ':'.
