<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Mail
 * @version    $Id$
 */

/*namespace Nette\Mail;*/



require_once dirname(__FILE__) . '/../Object.php';



/**
 * Sends e-mails via the PHP internal mail() function.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Mail
 */
class SendmailMailer extends /*Nette\*/Object implements IMailer
{

	/**
	 * Sends e-mail.
	 * @param  Mail
	 * @return bool
	 */
	function send(Mail $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(Mail::EOL . Mail::EOL, $tmp->generateMessage(), 2);
		$linux = strncasecmp(PHP_OS, 'win', 3);
		return mail(
			$mail->getEncodedHeader('To'),
			$mail->getEncodedHeader('Subject'),
			$linux ? $parts[1] : str_replace(Mail::EOL, "\r\n", $parts[1]),
			$linux ? $parts[0] : str_replace(Mail::EOL, "\r\n", $parts[0])
		);
	}

}
