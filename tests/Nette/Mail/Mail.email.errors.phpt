<?php

/**
 * Test: Nette\Mail\Message invalid email addresses.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Mail\Message;


require __DIR__ . '/../bootstrap.php';


$mail = new Message();

Assert::exception(function() use ($mail) {
	// From
	$mail->setFrom('John Doe <doe@example. com>');
}, 'Nette\Utils\AssertionException', "The header 'From' expects to be email, string 'doe@example. com' given.");


Assert::exception(function() use ($mail) {
	$mail->setFrom('John Doe <>');
}, 'Nette\Utils\AssertionException', "The header 'From' expects to be email, string '' given.");


Assert::exception(function() use ($mail) {
	$mail->setFrom('John Doe <doe@examplecom>');
}, 'Nette\Utils\AssertionException', "The header 'From' expects to be email, string 'doe@examplecom' given.");


Assert::exception(function() use ($mail) {
	$mail->setFrom('John Doe');
}, 'Nette\Utils\AssertionException', "The header 'From' expects to be email, string 'John Doe' given.");


Assert::exception(function() use ($mail) {
	$mail->setFrom('doe;@examplecom');
}, 'Nette\Utils\AssertionException', "The header 'From' expects to be email, string 'doe;@examplecom' given.");


Assert::exception(function() use ($mail) {
	// addReplyTo
	$mail->addReplyTo('@');
}, 'Nette\Utils\AssertionException', "The header 'Reply-To' expects to be email, string '@' given.");


Assert::exception(function() use ($mail) {
	// addTo
	$mail->addTo('@');
}, 'Nette\Utils\AssertionException', "The header 'To' expects to be email, string '@' given.");


Assert::exception(function() use ($mail) {
	// addCc
	$mail->addCc('@');
}, 'Nette\Utils\AssertionException', "The header 'Cc' expects to be email, string '@' given.");


Assert::exception(function() use ($mail) {
	// addBcc
	$mail->addBcc('@');
}, 'Nette\Utils\AssertionException', "The header 'Bcc' expects to be email, string '@' given.");
