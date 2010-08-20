<?php

/**
 * Test: Nette\Mail\Mail invalid email addresses.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Mail\Mail;



require __DIR__ . '/../initialize.php';



$mail = new Mail();

try {
	// From
	$mail->setFrom('John Doe <doe@example. com>');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@example. com' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <>');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <doe@examplecom>');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@examplecom' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe <doe@examplecom>');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe@examplecom' is not valid.", $e );
}


try {
	$mail->setFrom('John Doe');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'John Doe' is not valid.", $e );
}


try {
	$mail->setFrom('doe;@examplecom');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address 'doe;@examplecom' is not valid.", $e );
}


try {
	// addReplyTo
	$mail->addReplyTo('@');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addTo
	$mail->addTo('@');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addCc
	$mail->addCc('@');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}


try {
	// addBcc
	$mail->addBcc('@');
	Assert::failed();
} catch (Exception $e) {
	Assert::exception('InvalidArgumentException', "Email address '@' is not valid.", $e );
}
