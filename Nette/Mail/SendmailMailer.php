<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette;


/**
 * Sends emails via the PHP internal mail() function.
 *
 * @author     David Grudl
 */
class SendmailMailer extends Nette\Object implements IMailer
{
	/** @var string */
	public $commandArgs;


	/**
	 * Sends email.
	 * @return void
	 */
	public function send(Message $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Message::EOL . Message::EOL, $tmp->generateMessage(), 2);

		$args = array(
			$mail->getEncodedHeader('To'),
			$mail->getEncodedHeader('Subject'),
			str_replace(Message::EOL, PHP_EOL, $parts[1]),
			str_replace(Message::EOL, PHP_EOL, $parts[0]),
		);
		if ($this->commandArgs) {
			$args[] = (string) $this->commandArgs;
		}
		if (call_user_func_array('mail', $args) === FALSE) {
			$error = error_get_last();
			throw new Nette\InvalidStateException("Unable to send email: $error[message].");
		}
	}

}
