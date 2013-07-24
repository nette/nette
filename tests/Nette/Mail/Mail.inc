<?php

/**
 * Common code for Mail test cases.
 */

use Nette\Mail\Message,
	Nette\Mail\IMailer;


class TestMailer implements IMailer
{
	public static $output;

	function send(Message $mail)
	{
		self::$output = $mail->generateMessage();
	}

}
