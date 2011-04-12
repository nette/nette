<?php

/**
 * Common code for Mail test cases.
 */

use Nette\Mail\Mail;
use Nette\Mail\IMailer;



// Setup environment
Mail::$defaultMailer = 'TestMailer';


class TestMailer implements IMailer
{
	public static $output;

	function send(Mail $mail)
	{
		self::$output = $mail->generateMessage();
	}

}
