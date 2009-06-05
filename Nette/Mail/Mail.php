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
	 * @return Mail  provides a fluent interface
	 */
	public function setFrom($email, $name = NULL)
	{
		$this->setHeader('From', $this->formatEmail($email, $name));
		return $this;
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
	 * @return Mail  provides a fluent interface
	 */
	public function setSubject($subject)
	{
		$this->setHeader('Subject', $subject);
		return $this;
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
	 * @return Mail  provides a fluent interface
	 */
	public function setReturnPath($email)
	{
		$this->setHeader('Return-Path', $email);
		return $this;
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
	 * @return Mail  provides a fluent interface
	 */
	public function setPriority($priority)
	{
		$this->setHeader('X-Priority', (int) $priority);
		return $this;
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
	 * @return Mail  provides a fluent interface
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
		return $this;
	}



	/**
	 * Gets HTML body.
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->html;
	}



	/********************* building and sending ****************d*g**/



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
		return $this->inlines[$file] = $part;
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
		return $this->attachments[] = $part;
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



	/**
	 * Builds e-mail.
	 * @return void
	 */
	protected function build()
	{
		$mail = clone $this;
		$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
		$mail->setHeader('Message-ID', '<' . md5(uniqid('', TRUE)) . "@$hostname>");

		$cursor = $mail;
		if ($mail->attachments) {
			$tmp = $cursor->setContentType('multipart/mixed');
			$cursor = $cursor->addPart();
			foreach ($mail->attachments as $value) {
				$tmp->addPart($value);
			}
		}

		$html = $this->html;
		if ($html) {
			$tmp = $cursor->setContentType('multipart/alternative');
			$cursor = $cursor->addPart();
			$alt = $tmp->addPart();
			if ($mail->inlines) {
				$tmp = $alt->setContentType('multipart/related');
				$alt = $alt->addPart();
				foreach ($mail->inlines as $name => $value) {
					$tmp->addPart($value);
					$name = preg_quote($name, '#');
					$cid = substr($value->getHeader('Content-ID'), 1, -1);
					$html = preg_replace("#src=([\"'])$name\\1#", "src=\"cid:$cid\"", $html);
				}
			}
			$alt->setContentType('text/html', $mail->charset)->setEncoding(self::ENCODING_8BIT)->setBody($html);
		}

		$mail->setBody(NULL);
		$cursor->setContentType('text/plain', $mail->charset)->setEncoding(self::ENCODING_7BIT)->setBody($this->getBody());

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
