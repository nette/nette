<?php

/**
 * Test: Nette\Mail\Message invalid email addresses.
 *
 * @author     David Grudl
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';



$mail = new Message();

try {
	// From
	$mail->setFrom('John Doe <doe@example. com>');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@example. com' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <>');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <doe@examplecom>');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@examplecom' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <doe@examplecom>');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@examplecom' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'John Doe' is not valid.", $e );
}


try {
	$mail->setFrom('doe;@examplecom');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe;@examplecom' is not valid.", $e );
}


try {
	// addReplyTo
	$mail->addReplyTo('@');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addTo
	$mail->addTo('@');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addCc
	$mail->addCc('@');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addBcc
	$mail->addBcc('@');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}
