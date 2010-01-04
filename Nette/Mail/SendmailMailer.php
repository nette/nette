<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Mail
 */

/*namespace Nette\Mail;*/



/**
 * Sends e-mails via the PHP internal mail() function.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Mail
 */
class SendmailMailer extends /*Nette\*/Object implements IMailer
{

	/**
	 * Sends e-mail.
	 * @param  Mail
	 * @return void
	 */
	public function send(Mail $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Mail::EOL . Mail::EOL, $tmp->generateMessage(), 2);
		$linux = strncasecmp(PHP_OS, 'win', 3);

		/*Nette\*/Tools::tryError();
		$res = mail(
			$mail->getEncodedHeader('To'),
			$mail->getEncodedHeader('Subject'),
			$linux ? str_replace(Mail::EOL, "\n", $parts[1]) : $parts[1],
			$linux ? str_replace(Mail::EOL, "\n", $parts[0]) : $parts[0]
		);

		if (/*Nette\*/Tools::catchError($msg)) {
			throw new /*\*/InvalidStateException($msg);

		} elseif (!$res) {
			throw new /*\*/InvalidStateException('Unable to send email.');
		}
	}

}
