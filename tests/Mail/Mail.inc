<?php

/**
 * Common code for Mail test cases.
 */

use Nette\Mail\Mail;
use Nette\Mail\IMailer;



// Setup environment
Mail::$defaultMailer = 'FileMailer';


class FileMailer implements IMailer
{

	function send(Mail $mail)
	{
		echo $mail->generateMessage();
	}

}
