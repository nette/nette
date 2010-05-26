<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Mail
 */

namespace Nette\Mail;

use Nette;



/**
 * Mailer interface.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Mail
 */
interface IMailer
{

	/**
	 * Sends e-mail.
	 * @param  Mail
	 * @return void
	 */
	function send(Mail $mail);

}
