<?php

/**
 * Test: Nette\Mail\Message invalid headers.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';



$mail = new Message();

try {
	$mail->setHeader('', 'value');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Header name must be non-empty alphanumeric string, '' given.", $e );
}

try {
	$mail->setHeader(' name', 'value');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Header name must be non-empty alphanumeric string, ' name' given.", $e );
}

try {
	$mail->setHeader('n*ame', 'value');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Header name must be non-empty alphanumeric string, 'n*ame' given.", $e );
}
