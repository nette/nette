<?php

/**
 * Test: Nette\Mail\Message invalid headers.
 *
 * @author     David Grudl
 * @package    Nette\Mail
 */

use Nette\Mail\Message;


require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Mail.inc';


$mail = new Message();

Assert::exception(function() use ($mail) {
	$mail->setHeader('', 'value');
}, 'InvalidArgumentException', "Header name must be non-empty alphanumeric string, '' given.");

Assert::exception(function() use ($mail) {
	$mail->setHeader(' name', 'value');
}, 'InvalidArgumentException', "Header name must be non-empty alphanumeric string, ' name' given.");

Assert::exception(function() use ($mail) {
	$mail->setHeader('n*ame', 'value');
}, 'InvalidArgumentException', "Header name must be non-empty alphanumeric string, 'n*ame' given.");
