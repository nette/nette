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
 * @package    Nette\Web
 * @version    $Id$
 */

/*namespace Nette\Web;*/



require_once dirname(__FILE__) . '/../Web/MailMimePart.php';



/**
 * Mail MIME part.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
 */
class Mail extends MailMimePart
{
	/**#@+ Priority */
	const HIGH = 1;
	const NORMAL = 3;
	const LOW = 5;
	/**#@-*/

	/** @var callback */
	public static $mailer = array(__CLASS__, 'defaultMailer');

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
		$this->addHeader('MIME-Version', '1.0');
		$this->addHeader('X-Mailer', 'Nette Framework');
	}



	/**
	 * Sets the sender of the message.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function setFrom($email, $name = NULL)
	{
		$this->addHeader('From', NULL);
		$this->addRecipient('From', $email, $name);
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
		$this->addHeader('Subject', $subject);
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
		$this->addRecipient('To', $email, $name);
	}



	/**
	 * Adds carbon copy email recipient.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function addCc($email, $name = NULL)
	{
		$this->addRecipient('Cc', $email, $name);
	}



	/**
	 * Adds blind carbon copy email recipient.
	 * @param  string  e-mail or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return void
	 */
	public function addBcc($email, $name = NULL)
	{
		$this->addRecipient('Bcc', $email, $name);
	}



	/**
	 * Adds email recipient.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 */
	private function addRecipient($header, $email, $name)
	{
		if (!$name && preg_match('#^(.+) +<(.*)>$#', $email, $matches)) {
			list(, $name, $email) = $matches;
		}

		if (!$name) {
			$name = $email;
		} elseif ($this->encodeHeader($name) === $name && strpos($name, ',') !== FALSE) {
			$name = "\"$name\" <$email>";
		} else {
			$name = "$name <$email>";
		}

		$this->addHeader($header, array($email => $name));
	}



	/**
	 * Sets email priority.
	 * @param  int
	 * @return void
	 */
	public function setPriority($priority)
	{
		$this->addHeader('X-Priority', (int) $priority);
	}



	/**
	 * Returns email priority.
	 * @return int
	 */
	public function getPriority()
	{
		return $this->getHeader('X-Priority', self::NORMAL);
	}



	/**
	 * Sets HTML body.
	 * @param  string
	 * @return void
	 */
	public function setHtmlBody($html)
	{
		$this->html = (string) $html;
		if ($this->getBody() === '') {
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
	 * @return MailMimePart
	 */
	public function addEmbeddedFile($file, $contentType = 'application/octet-stream', $encoding = self::ENCODING_BASE64)
	{
		$part = new MailMimePart;
		$part->setContentType($contentType);
		$part->setEncoding($encoding);
		$part->setBody(file_get_contents($file));
		$part->addHeader('Content-Disposition', 'inline; filename="' . basename($file) . '"');
		$part->addHeader('Content-ID', '<' . md5(uniqid('', TRUE)) . '>');
		return $this->inlines[basename($file)] = $part;
	}



	/**
	 * Adds attachment.
	 * @param  string
	 * @return MailMimePart
	 */
	public function addAttachment($file, $contentType = 'application/octet-stream', $encoding = self::ENCODING_BASE64)
	{
		$part = new MailMimePart;
		$part->setContentType($contentType);
		$part->setEncoding($encoding);
		$part->setBody(file_get_contents($file));
		$part->addHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"');
		return $this->attachments[$file] = $part;
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



	private function injectInlines($part)
	{
		foreach ($this->inlines as $value) {
			$part->addPart($value);
		}
	}



	private function injectAttachments($part)
	{
		foreach ($this->attachments as $value) {
			$part->addPart($value);
		}
	}



	/**
	 * Builds e-mail.
	 * @return void
	 */
	protected function build()
	{
		$message = clone $this;

		$html = $this->html;
		foreach ($this->inlines as $name => $value) {
			$name = preg_quote($name);
			$cid = substr($value->getHeader('Content-ID'), 1, -1);
			$html = preg_replace("#src=([\"'])$name\\1#", "src=\"cid:$cid\"", $html);
		}

		if (!$this->html && !$this->attachments) {
			$this->injectText($message);

		} elseif (!$this->html && $this->attachments) {
			$message->setContentType('multipart/mixed');
			$this->injectText($message->createPart());
			$this->injectAttachments($message);

		} elseif (!$this->attachments && !$this->inlines) {
			$message->setContentType('multipart/alternative');
			$this->injectText($message->createPart());
			$this->injectHtml($message->createPart(), $html);

		} elseif (!$this->attachments && $this->inlines) {
			$message->setContentType('multipart/related');
			$alternative = $message->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			$this->injectInlines($message);

		} elseif ($this->attachments && !$this->inlines) {
			$message->setContentType('multipart/mixed');
			$alternative = $message->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			$this->injectAttachments($message);

		} elseif ($this->attachments && $this->inlines) {
			$message->setContentType('multipart/mixed');
			$related = $message->createPart('multipart/related');
			$alternative = $related->createPart('multipart/alternative');
			$this->injectText($alternative->createPart());
			$this->injectHtml($alternative->createPart(), $html);
			$this->injectInlines($related);
			$this->injectAttachments($message);

		} else {
			throw new /*\*/InvalidStateException;
		}

		$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
		$message->addHeader('Message-ID', sprintf('<%s@%s>', md5(uniqid('', TRUE)), $hostname));

		return $message;
	}



	/**
	 * Sends e-mail.
	 * @return void
	 */
	public function send()
	{
		return call_user_func(self::$mailer, $this->build());
	}



	/**
	 * Default mailer.
	 * @param  Mail
	 * @return bool
	 */
	private static function defaultMailer($message)
	{
		$body = $message->getEncodedBody();

		$headers = $headersS = array();
		foreach ($message->getHeaders() as $name => $value) {
			$headers[$name] = self::encodeHeader(is_array($value) ? implode(',', $value) : $value, $message->charset);
			$headersS[$name] = $name . ': ' . $headers[$name];
		}

		unset($headersS['Subject'], $headersS['To']);
		$headersS = implode(self::EOL, $headersS);

		if (PHP_OS !== 'Linux') {
			$body = str_replace(self::EOL, "\r\n", $body);
			$headersS = str_replace(self::EOL, "\r\n", $headersS);
		}

		return mail(
			isset($headers['To']) ? $headers['To'] : '',
			isset($headers['Subject']) ? $headers['Subject'] : '',
			$body,
			$headersS
		);
	}

}
