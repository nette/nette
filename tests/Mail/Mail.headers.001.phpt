<?php

/**
 * Test: Nette\Mail\Mail invalid headers.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

/*use Nette\Mail\Mail;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';

require dirname(__FILE__) . '/Mail.inc';



$mail = new Mail();

try {
	output();
	$mail->setHeader('', 'value');
} catch (Exception $e) {
	dump( $e );
}

try {
	output();
	$mail->setHeader(' name', 'value');
} catch (Exception $e) {
	dump( $e );
}

try {
	output();
	$mail->setHeader('n*ame', 'value');
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler() ?>

------EXPECT------
===

Exception InvalidArgumentException: Header name must be non-empty alphanumeric string, '' given.

===

Exception InvalidArgumentException: Header name must be non-empty alphanumeric string, ' name' given.

===

Exception InvalidArgumentException: Header name must be non-empty alphanumeric string, 'n*ame' given.
