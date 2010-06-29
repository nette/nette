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
	T::note('From');
	$mail->setFrom('John Doe <doe@example. com>');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note();
	$mail->setFrom('John Doe <>');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note();
	$mail->setFrom('John Doe <doe@examplecom>');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note();
	$mail->setFrom('John Doe <doe@examplecom>');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note();
	$mail->setFrom('John Doe');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note();
	$mail->setFrom('doe;@examplecom');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note('addReplyTo');
	$mail->addReplyTo('@');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note('addTo');
	$mail->addTo('@');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note('addCc');
	$mail->addCc('@');
} catch (Exception $e) {
	T::dump( $e );
}


try {
	T::note('addBcc');
	$mail->addBcc('@');
} catch (Exception $e) {
	T::dump( $e );
}


__halt_compiler() ?>

------EXPECT------
From

Exception InvalidArgumentException: Email address 'doe@example. com' is not valid.

===

Exception InvalidArgumentException: Email address '' is not valid.

===

Exception InvalidArgumentException: Email address 'doe@examplecom' is not valid.

===

Exception InvalidArgumentException: Email address 'doe@examplecom' is not valid.

===

Exception InvalidArgumentException: Email address 'John Doe' is not valid.

===

Exception InvalidArgumentException: Email address 'doe;@examplecom' is not valid.

addReplyTo

Exception InvalidArgumentException: Email address '@' is not valid.

addTo

Exception InvalidArgumentException: Email address '@' is not valid.

addCc

Exception InvalidArgumentException: Email address '@' is not valid.

addBcc

Exception InvalidArgumentException: Email address '@' is not valid.
