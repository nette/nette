<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Mail;

use Nette;



/**
 * Sends e-mails via the PHP internal mail() function.
 *
 * @author     David Grudl
 */
class SendmailMailer extends Nette\Object implements IMailer
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

		Nette\Tools::tryError();
		$res = mail(
			str_replace(Mail::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
			str_replace(Mail::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
			str_replace(Mail::EOL, PHP_EOL, $parts[1]),
			str_replace(Mail::EOL, PHP_EOL, $parts[0])
		);

		if (Nette\Tools::catchError($msg)) {
			throw new \InvalidStateException($msg);

		} elseif (!$res) {
			throw new \InvalidStateException('Unable to send email.');
		}
	}

}
