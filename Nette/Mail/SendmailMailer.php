<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
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

	/**
	 * Sends email.
	 * @param  Message
	 * @return void
	 */
	public function send(Message $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Message::EOL . Message::EOL, $tmp->generateMessage(), 2);

		Nette\Diagnostics\Debugger::tryError();
		$res = mail(
			str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
			str_replace(Message::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
			str_replace(Message::EOL, PHP_EOL, $parts[1]),
			str_replace(Message::EOL, PHP_EOL, $parts[0])
		);

		if (Nette\Diagnostics\Debugger::catchError($e)) {
			throw new Nette\InvalidStateException('mail(): ' . $e->getMessage(), 0, $e);

		} elseif (!$res) {
			throw new Nette\InvalidStateException('Unable to send email.');
		}
	}

}
