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



require_once dirname(__FILE__) . '/../Mail/MailMimePart.php';



/**
 * Mail provides functionality to compose and send both text and MIME-compliant multipart e-mail messages.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Mail
 *
 * @property   string $from
 * @property   string $subject
 * @property   string $returnPath
 * @property   int $priority
 * @property   string $htmlBody
 */
class Mail extends MailMimePart
{
	/**#@+ Priority */
	const HIGH = 1;
	const NORMAL = 3;
	const LOW = 5;
	/**#@-*/

	/** @var IMailer */
	public static $defaultMailer = 'Nette\Mail\SendmailMailer';

	/** @var array */
	public static $defaultHeaders = array(
		'MIME-Version' => '1.0',
		'X-Mailer' => 'Nette Framework',
	);

	/** @var string */
	private $charset = 'UTF-8';

	/** @var array */
	private $attachments = array();

	/** @var array */
	private $inlines = array();

	/** @var string */
	private $html = '';




	public function __construct()
	{
		foreach (self::$defaultHeaders as $name => $value) {
			$this->setHeader($name, $value);
		}
	}



	/**
	 * Sets the sender of the message.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function setFrom($email, $name = NULL)
	{
		$this->setHeader('From', $this->formatEmail($email, $name));
	}



	/**
	 * Returns the sender of the message.
	 * @return string
	 */
	public function getFrom()
	{
		return $this->getHeader('From');
	}



	/**
	 * Sets the subject of the message.
	 * @param  string
	 * @return void
	 */
	public function setSubject($subject)
	{
		$this->setHeader('Subject', $subject);
	}



	/**
	 * Returns the subject of the message.
	 * @return string
	 */
	public function getSubject()
	{
		return $this->getHeader('Subject');
	}



	/**
	 * Adds email recipient.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function addTo($email, $name = NULL) // addRecipient()
	{
		$this->setHeader('To', $this->formatEmail($email, $name), TRUE);
	}



	/**
	 * Adds carbon copy email recipient.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function addCc($email, $name = NULL)
	{
		$this->setHeader('Cc', $this->formatEmail($email, $name), TRUE);
	}



	/**
	 * Adds blind carbon copy email recipient.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function addBcc($email, $name = NULL)
	{
		$this->setHeader('Bcc', $this->formatEmail($email, $name), TRUE);
	}



	/**
	 * Formats recipient e-mail.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	private function formatEmail($email, $name)
	{
		if (!$name && preg_match('#^(.+) +<(.*)>$#', $email, $matches)) {
			list(, $name, $email) = $matches;
		}

		$name = preg_replace('#[\r\n\t"<>]#', '', $name);
		$email = preg_replace('#[\r\n\t"<>,]#', '', $email);
		if (!$name) {
			return $email;

		} elseif (self::encodeQuotedPrintableHeader($name) === $name && strpos($name, ',') !== FALSE) {
			return "\"$name\" <$email>";

		} else {
			return "$name <$email>";
		}
	}



	/**
	 * Sets the Return-Path header of the message.
	 * @param  string  e-mail
	 * @return void
	 */
	public function setReturnPath($email)
	{
		$this->setHeader('Return-Path', $email);
	}



	/**
	 * Returns the Return-Path header.
	 * @return string
	 */
	public function getReturnPath()
	{
		return $this->getHeader('From');
	}



	/**
	 * Sets email priority.
	 * @param  int
	 * @return void
	 */
	public function setPriority($priority)
	{
		$this->setHeader('X-Priority', (int) $priority);
	}



	/**
	 * Returns email priority.
	 * @return int
	 */
	public function getPriority()
	{
		return $this->getHeader('X-Priority');
	}



	/**
	 * Sets HTML body.
	 * @param  string
	 * @return void
	 */
	public function setHtmlBody($html)
	{
		$this->html = (string) $html;
		if ($this->getBody() === '') { // TODO: better
			$text = strip_tags($html);
			$text = html_entity_decode($text, ENT_QUOTES, $this->charset);
			$text = preg_replace('#<style.*</style>#Uis', '', $text);
			$text = preg_replace('#<script.*</script>#Uis', '', $text);
			$this->setBody($text);
		}
	}



	/**
	 * Gets HTML body.
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->html;
	}



	/**
	 * Adds embedded file.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MailMimePart
	 */
	public function addEmbeddedFile($file, $contentType = NULL, $encoding = self::ENCODING_BASE64)
	{
		$part = $this->createFilePart($file, $contentType, $encoding);
		$part->setHeader('Content-Disposition', 'inline; filename="' . basename($file) . '"');
		$part->setHeader('Content-ID', '<' . md5(uniqid('', TRUE)) . '>');
		return $this->inlines[basename($file)] = $part;
	}



	/**
	 * Adds attachment.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MailMimePart
	 */
	public function addAttachment($file, $contentType = NULL, $encoding = self::ENCODING_BASE64)
	{
		$part = $this->createFilePart($file, $contentType, $encoding);
		$part->setHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"');
		return $this->attachments[$file] = $part;
	}



	/**
	 * Creates file MIME part.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MailMimePart
	 */
	public function createFilePart($file, $contentType, $encoding)
	{
		if (!is_file($file)) {
			throw new /*\*/FileNotFoundException("File '$file' not found.");
		}
		if (!$contentType) {
			$info = getimagesize($file);
			$contentType = $info ? image_type_to_mime_type($info[2]) : 'application/octet-stream';
		}
		$part = new MailMimePart;
		$part->setContentType($contentType);
		$part->setEncoding($encoding);
		$part->setBody(file_get_contents($file));
		return $part;
	}



	private function injectText($part)
	{
		$part->setContentType('text/plain', $this->charset);
		$part->setEncoding(self::ENCODING_7BIT);
		$part->setBody($this->getBody());
	}



	private function injectHtml($part, $html)
	{
		$part->setContentType('text/html', $this->charset);
		$part->setEncoding(self::ENCODING_QUOTED_PRINTABLE);
		$part->setBody($html);
	}



	/**
	 * Builds e-mail.
	 * @return void
	 */
	protected function build()
	{
		$mail = clone $this;
		$mail->setBody(NULL);

		$html = $this->html;
		foreach ($this->inlines as $name => $value) {
			$name = preg_quote($name);
			$cid = substr($value->getHeader('Content-ID'), 1, -1);
			$html = preg_replace("#src=([\"'])$name\\1#", "src=\"cid:$cid\"", $html);
		}

		if (!$this->html && !$this->attachments) {
			$this->injectText($mail);

		} elseif (!$this->html && $this->attachments) {
			$mail->setContentType('multipart/mixed');
			$this->injectText($mail->createPart());
			foreach ($this->attachments as $value) {
				$mail->addPart($value);
			}

		} elseif (!$this->attachments && !$this->inlines) {
			$mail->setContentType('multipart/alternative');
			$this->injectText($mail->createPart());
			$this->injectHtml($mail->createPart(), $html);

		} elseif (!$this->attachments && $this->inlines) {
			$mail->setContentType('multipart/related');
			$alternative = $mail->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			foreach ($this->inlines as $value) {
				$mail->addPart($value);
			}

		} elseif ($this->attachments && !$this->inlines) {
			$mail->setContentType('multipart/mixed');
			$alternative = $mail->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			foreach ($this->attachments as $value) {
				$mail->addPart($value);
			}

		} elseif ($this->attachments && $this->inlines) {
			$mail->setContentType('multipart/mixed');
			$related = $mail->createPart('multipart/related');
			$alternative = $related->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			foreach ($this->inlines as $value) {
				$related->addPart($value);
			}
			foreach ($this->attachments as $value) {
				$mail->addPart($value);
			}

		} else {
			throw new /*\*/InvalidStateException;
		}

		$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
		$mail->setHeader('Message-ID', sprintf('<%s@%s>', md5(uniqid('', TRUE)), $hostname));

		return $mail;
	}



	/**
	 * Sends e-mail.
	 * @param  IMailer
	 * @return bool
	 */
	public function send(IMailer $mailer = NULL)
	{
		if ($mailer === NULL) {
			/**/fixCallback(self::$defaultMailer);/**/
			$mailer = is_object(self::$defaultMailer) ? self::$defaultMailer : new self::$defaultMailer;
		}
		return $mailer->send($this->build());
	}

}
