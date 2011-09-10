<?php

/**
 * Test: Nette\Mail\Message invalid email addresses.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 * @subpackage UnitTests
 */

use Nette\Mail\Message;



require __DIR__ . '/../bootstrap.php';



$mail = new Message();

Assert::throws(function() use ($mail) {
	// From
	$mail->setFrom('John Doe <doe@example. com>');
}, 'InvalidArgumentException', "Email address 'doe@example. com' is not valid.");


Assert::throws(function() use ($mail) {
	$mail->setFrom('John Doe <>');
}, 'InvalidArgumentException', "Email address '' is not valid.");


Assert::throws(function() use ($mail) {
	$mail->setFrom('John Doe <doe@examplecom>');
}, 'InvalidArgumentException', "Email address 'doe@examplecom' is not valid.");


Assert::throws(function() use ($mail) {
	$mail->setFrom('John Doe <doe@examplecom>');
}, 'InvalidArgumentException', "Email address 'doe@examplecom' is not valid.");


Assert::throws(function() use ($mail) {
	$mail->setFrom('John Doe');
}, 'InvalidArgumentException', "Email address 'John Doe' is not valid.");


Assert::throws(function() use ($mail) {
	$mail->setFrom('doe;@examplecom');
}, 'InvalidArgumentException', "Email address 'doe;@examplecom' is not valid.");


Assert::throws(function() use ($mail) {
	// addReplyTo
	$mail->addReplyTo('@');
}, 'InvalidArgumentException', "Email address '@' is not valid.");


Assert::throws(function() use ($mail) {
	// addTo
	$mail->addTo('@');
}, 'InvalidArgumentException', "Email address '@' is not valid.");


Assert::throws(function() use ($mail) {
	// addCc
	$mail->addCc('@');
}, 'InvalidArgumentException', "Email address '@' is not valid.");


Assert::throws(function() use ($mail) {
	// addBcc
	$mail->addBcc('@');
}, 'InvalidArgumentException', "Email address '@' is not valid.");
