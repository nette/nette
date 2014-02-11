<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Mail;

use Nette,
	Nette\Utils\Strings;


/**
 * Mail provides functionality to compose and send both text and MIME-compliant multipart email messages.
 *
 * @author     David Grudl
 *
 * @property   array $from
 * @property   string $subject
 * @property   string $returnPath
 * @property   int $priority
 * @property   mixed $htmlBody
 * @property   IMailer $mailer
 */
class Message extends MimePart
{
	/** Priority */
	const HIGH = 1,
		NORMAL = 3,
		LOW = 5;

	/** @deprecated */
	public static $defaultMailer = 'Nette\Mail\SendmailMailer';

	/** @var array */
	public static $defaultHeaders = array(
		'MIME-Version' => '1.0',
		'X-Mailer' => 'Nette Framework',
	);

	/** @var IMailer */
	private $mailer;

	/** @var array */
	private $attachments = array();

	/** @var array */
	private $inlines = array();

	/** @var mixed */
	private $html;


	public function __construct()
	{
		foreach (static::$defaultHeaders as $name => $value) {
			$this->setHeader($name, $value);
		}
		$this->setHeader('Date', date('r'));
	}


	/**
	 * Sets the sender of the message.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function setFrom($email, $name = NULL)
	{
		$this->setHeader('From', $this->formatEmail($email, $name));
		return $this;
	}


	/**
	 * Returns the sender of the message.
	 * @return array
	 */
	public function getFrom()
	{
		return $this->getHeader('From');
	}


	/**
	 * Adds the reply-to address.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function addReplyTo($email, $name = NULL)
	{
		$this->setHeader('Reply-To', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Sets the subject of the message.
	 * @param  string
	 * @return self
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
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function addTo($email, $name = NULL) // addRecipient()
	{
		$this->setHeader('To', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Adds carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function addCc($email, $name = NULL)
	{
		$this->setHeader('Cc', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Adds blind carbon copy email recipient.
	 * @param  string  email or format "John Doe" <doe@example.com>
	 * @param  string
	 * @return self
	 */
	public function addBcc($email, $name = NULL)
	{
		$this->setHeader('Bcc', $this->formatEmail($email, $name), TRUE);
		return $this;
	}


	/**
	 * Formats recipient email.
	 * @param  string
	 * @param  string
	 * @return array
	 */
	private function formatEmail($email, $name)
	{
		if (!$name && preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			return array($matches[2] => $matches[1]);
		} else {
			return array($email => $name);
		}
	}


	/**
	 * Sets the Return-Path header of the message.
	 * @param  string  email
	 * @return self
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
		return $this->getHeader('Return-Path');
	}


	/**
	 * Sets email priority.
	 * @param  int
	 * @return self
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
	 * @param  string|Nette\Templating\ITemplate
	 * @param  mixed base-path or FALSE to disable parsing
	 * @return self
	 */
	public function setHtmlBody($html, $basePath = NULL)
	{
		if ($html instanceof Nette\Templating\ITemplate) {
			$html->mail = $this;
			if ($basePath === NULL && $html instanceof Nette\Templating\IFileTemplate) {
				$basePath = dirname($html->getFile());
			}
			$html = $html->__toString(TRUE);
		}

		if ($basePath !== FALSE) {
			$cids = array();
			$matches = Strings::matchAll(
				$html,
				'#(src\s*=\s*|background\s*=\s*|url\()(["\']?)(?![a-z]+:|[/\\#])([^"\')\s]+)#i',
				PREG_OFFSET_CAPTURE
			);
			foreach (array_reverse($matches) as $m) {
				$file = rtrim($basePath, '/\\') . '/' . $m[3][0];
				if (!isset($cids[$file])) {
					$cids[$file] = substr($this->addEmbeddedFile($file)->getHeader("Content-ID"), 1, -1);
				}
				$html = substr_replace($html,
					"{$m[1][0]}{$m[2][0]}cid:{$cids[$file]}",
					$m[0][1], strlen($m[0][0])
				);
			}
		}
		$this->html = $html;

		if ($this->getSubject() == NULL && $matches = Strings::match($html, '#<title>(.+?)</title>#is')) { // intentionally ==
			$this->setSubject(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
		}

		if ($this->getBody() == NULL && $html != NULL) { // intentionally ==
			$this->setBody($this->buildText($html));
		}
		return $this;
	}


	/**
	 * Gets HTML body.
	 * @return mixed
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
	 * @return MimePart
	 */
	public function addEmbeddedFile($file, $content = NULL, $contentType = NULL)
	{
		return $this->inlines[$file] = $this->createAttachment($file, $content, $contentType, 'inline')
			->setHeader('Content-ID', $this->getRandomId());
	}


	/**
	 * Adds attachment.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MimePart
	 */
	public function addAttachment($file, $content = NULL, $contentType = NULL)
	{
		return $this->attachments[] = $this->createAttachment($file, $content, $contentType, 'attachment');
	}


	/**
	 * Creates file MIME part.
	 * @return MimePart
	 */
	private function createAttachment($file, $content, $contentType, $disposition)
	{
		$part = new MimePart;
		if ($content === NULL) {
			$content = @file_get_contents($file); // intentionally @
			if ($content === FALSE) {
				throw new Nette\FileNotFoundException("Unable to read file '$file'.");
			}
		} else {
			$content = (string) $content;
		}
		$part->setBody($content);
		$part->setContentType($contentType ? $contentType : Nette\Utils\MimeTypeDetector::fromString($content));
		$part->setEncoding(preg_match('#(multipart|message)/#A', $contentType) ? self::ENCODING_8BIT : self::ENCODING_BASE64);
		$part->setHeader('Content-Disposition', $disposition . '; filename="' . Strings::fixEncoding(basename($file)) . '"');
		return $part;
	}


	/********************* building and sending ****************d*g**/


	/**
	 * @deprecated
	 */
	public function send()
	{
		trigger_error(__METHOD__ . '() is deprecated; use IMailer::send() instead.', E_USER_DEPRECATED);
		$this->getMailer()->send($this);
	}


	/**
	 * @deprecated
	 */
	public function setMailer(IMailer $mailer)
	{
		//trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		$this->mailer = $mailer;
		return $this;
	}


	/**
	 * @deprecated
	 */
	public function getMailer()
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
		if ($this->mailer === NULL) {
			$this->mailer = is_object(static::$defaultMailer) ? static::$defaultMailer : new static::$defaultMailer;
		}
		return $this->mailer;
	}


	/**
	 * Returns encoded message.
	 * @return string
	 */
	public function generateMessage()
	{
		return $this->build()->getEncodedMessage();
	}


	/**
	 * Builds email. Does not modify itself, but returns a new object.
	 * @return Message
	 */
	protected function build()
	{
		$mail = clone $this;
		$mail->setHeader('Message-ID', $this->getRandomId());

		$cursor = $mail;
		if ($mail->attachments) {
			$tmp = $cursor->setContentType('multipart/mixed');
			$cursor = $cursor->addPart();
			foreach ($mail->attachments as $value) {
				$tmp->addPart($value);
			}
		}

		if ($mail->html != NULL) { // intentionally ==
			$tmp = $cursor->setContentType('multipart/alternative');
			$cursor = $cursor->addPart();
			$alt = $tmp->addPart();
			if ($mail->inlines) {
				$tmp = $alt->setContentType('multipart/related');
				$alt = $alt->addPart();
				foreach ($mail->inlines as $value) {
					$tmp->addPart($value);
				}
			}
			$alt->setContentType('text/html', 'UTF-8')
				->setEncoding(preg_match('#\S{990}#', $mail->html)
					? self::ENCODING_QUOTED_PRINTABLE
					: (preg_match('#[\x80-\xFF]#', $mail->html) ? self::ENCODING_8BIT : self::ENCODING_7BIT))
				->setBody($mail->html);
		}

		$text = $mail->getBody();
		$mail->setBody(NULL);
		$cursor->setContentType('text/plain', 'UTF-8')
			->setEncoding(preg_match('#\S{990}#', $text)
				? self::ENCODING_QUOTED_PRINTABLE
				: (preg_match('#[\x80-\xFF]#', $text) ? self::ENCODING_8BIT : self::ENCODING_7BIT))
			->setBody($text);

		return $mail;
	}


	/**
	 * Builds text content.
	 * @return string
	 */
	protected function buildText($html)
	{
		$text = Strings::replace($html, array(
			'#<(style|script|head).*</\\1>#Uis' => '',
			'#<t[dh][ >]#i' => " $0",
			'#[\r\n]+#' => ' ',
			'#<(/?p|/?h\d|li|br|/tr)[ >/]#i' => "\n$0",
		));
		$text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
		$text = Strings::replace($text, '#[ \t]+#', ' ');
		return trim($text);
	}


	/** @return string */
	private function getRandomId()
	{
		return '<' . Strings::random() . '@'
			. preg_replace('#[^\w.-]+#', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : php_uname('n'))
			. '>';
	}

}
