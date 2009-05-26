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
 * Mail Mime Part.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Mail
 */
class MailMimePart extends /*Nette\*/Object
{
	/**#@+ Encoding */
	const ENCODING_BASE64 = 'base64';
	const ENCODING_7BIT = '7bit';
	const ENCODING_8BIT = '8bit';
	const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
	/**#@-*/

	const EOL = "\n";

	/** @var array */
	private $headers = array();

	/** @var array */
	private $parts = array();

	/** @var string */
	private $body = '';



	/**
	 * Adds user header.
	 * @param  string|array
	 * @param  string
	 * @return void
	 */
	public function addHeader($name, $value)
	{
		if ($value == '') { // intentionally ==
			unset($this->headers[$name]);

		} elseif (is_array($value)) {
			$this->headers[$name][key($value)] = current($value);

		} else {
			$this->headers[$name] = $value;
		}
	}



	/**
	 * Returns a header.
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function getHeader($name, $default = NULL)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : $default;
	}



	/**
	 * Returns all headers.
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * Sets Content-Type header.
	 * @param  string
	 * @return void
	 */
	public function setContentType($contentType, $charset = NULL)
	{
		$this->headers['Content-Type'] = $contentType . ($charset ? "; charset=$charset" : '');
	}



	/**
	 * Sets Content-Transfer-Encoding header.
	 * @param  string
	 * @return void
	 */
	public function setEncoding($encoding)
	{
		$this->addHeader('Content-Transfer-Encoding', $encoding);
	}



	/**
	 * Returns Content-Transfer-Encoding header.
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->getHeader('Content-Transfer-Encoding');
	}



	/**
	 * Adds new multipart.
	 * @param  MailMimePart
	 * @return void
	 */
	public function addPart(MailMimePart $part)
	{
		$this->parts[] = $part;
	}



	/**
	 * Creates new multipart.
	 * @return MailMimePart
	 */
	public function createPart($contentType = NULL)
	{
		$part = new self;
		$part->setContentType($contentType);
		return $this->parts[] = $part;
	}



	/**
	 * Sets textual body.
	 * @param  string
	 * @return void
	 */
	public function setBody($text)
	{
		$this->body = (string) $text;
	}



	/**
	 * Gets textual body.
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}



	/**
	 * Encodes header.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	protected static function encodeHeader($header, $charset = 'UTF-8')
	{
		preg_match_all('#[^? ]*[\x7F-\xFF]+[^? ]*#', $header, $matches);
		foreach ($matches[0] as $match) {
			$replacement = preg_replace('#[=\x7F-\xFF]#e', '"=" . strtoupper(dechex(ord("\0")))', $match);
			$header = str_replace($match, '=?' . $charset . '?Q?' . $replacement . '?=', $header);
		}
		return $header;
	}


	/**
	 * Returns encoded body.
	 * @return string
	 */
	protected function getEncodedBody()
	{
		if ($this->parts) {
			$boundary = '=_' . md5(uniqid('', TRUE));
			$this->headers['Content-Type'] .= ';' . self::EOL . "\tboundary=\"$boundary\"";

			$output = '';
			foreach ($this->parts as $part) {
				$body = $part->getEncodedBody();
				$output .= '--' . $boundary;
				foreach ($part->headers as $name => $value) {
					$output .= self::EOL . $name . ': ' . $this->encodeHeader($value/*, $this->charset*/);
				}
				$output .= self::EOL . self::EOL . $body . self::EOL;
			}
			return $output . '--' . $boundary.'--';
		}

		if ($this->body == '') {
			return '';
		}

		switch ($this->getEncoding()) {
			case self::ENCODING_QUOTED_PRINTABLE:
				$out = '';
				$s = preg_replace('#[=\x00-\x1F\x7F-\xFF]#e', '"=" . strtoupper(dechex(ord("\0")))', $this->body);
				$s = rtrim($s);

				while ($s) {
					$ptr = strlen($s);
					if ($ptr > 72) {
						$ptr = 72;
					}

					$pos = strrpos(substr($s, 0, $ptr), '=');
					if ($pos !== FALSE && $pos >= $ptr - 2) {
						$ptr = $pos;
					}

					if ($ptr > 0 && $s[$ptr - 1] == ' ') {
						--$ptr;
					}

					$out .= substr($s, 0, $ptr) . '=' . self::EOL;
					$s = substr($s, $ptr);
				}

				$out = rtrim($out, self::EOL);
				$out = rtrim($out, '=');
				return $out;

			case self::ENCODING_BASE64:
				return rtrim(chunk_split(base64_encode($this->body), 76, self::EOL));

			case self::ENCODING_7BIT:
			case self::ENCODING_8BIT:
				return $this->body;

			default:
				throw new /*\*/InvalidStateException('Unknown encoding');
		}
	}

}
