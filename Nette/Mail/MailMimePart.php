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
 * MIME message part.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Mail
 *
 * @property   string $encoding
 * @property   string $body
 * @property-read array $headers
 */
class MailMimePart extends /*Nette\*/Object
{
	/**#@+ Encoding */
	const ENCODING_BASE64 = 'base64';
	const ENCODING_7BIT = '7bit';
	const ENCODING_8BIT = '8bit';
	const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
	/**#@-*/

	const EOL = "\r\n";
	const LINE_LENGTH = 78;

	/** @var array */
	private $headers = array();

	/** @var array */
	private $parts = array();

	/** @var string */
	private $body;



	/**
	 * Sets an user header.
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return MailMimePart  provides a fluent interface
	 */
	public function setHeader($name, $value, $append = FALSE)
	{
		if (!$name || preg_match('#[^a-z0-9-]#i', $name)) {
			throw new /*\*/InvalidArgumentException("Header name must be non-empty alphanumeric string, '$name' given.");
		}

		$value = preg_replace('#[\r\n\t]#', '', $value);
		if ($value == '') { // intentionally ==
			if (!$append) {
				unset($this->headers[$name]);
			}

		} elseif ($append) {
			$this->headers[$name][] = $value;

		} else {
			$this->headers[$name] = $value;
		}
		return $this;
	}



	/**
	 * Returns a header.
	 * @param  string
	 * @return mixed
	 */
	public function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : NULL;
	}



	/**
	 * Returns an encoded header.
	 * @param  string
	 * @return string
	 */
	public function getEncodedHeader($name)
	{
		return isset($this->headers[$name]) ? self::encodeQuotedPrintableHeader($this->headers[$name]) : NULL;
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
	 * @param  string
	 * @return MailMimePart  provides a fluent interface
	 */
	public function setContentType($contentType, $charset = NULL)
	{
		$this->setHeader('Content-Type', $contentType . ($charset ? "; charset=$charset" : ''));
		return $this;
	}



	/**
	 * Sets Content-Transfer-Encoding header.
	 * @param  string
	 * @return MailMimePart  provides a fluent interface
	 */
	public function setEncoding($encoding)
	{
		$this->setHeader('Content-Transfer-Encoding', $encoding);
		return $this;
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
	 * Adds or creates new multipart.
	 * @param  MailMimePart
	 * @return MailMimePart
	 */
	public function addPart(MailMimePart $part = NULL)
	{
		return $this->parts[] = $part === NULL ? new self : $part;
	}



	/**
	 * Sets textual body.
	 * @param  mixed
	 * @return MailMimePart  provides a fluent interface
	 */
	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}



	/**
	 * Gets textual body.
	 * @return mixed
	 */
	public function getBody()
	{
		return $this->body;
	}



	/********************* building ****************d*g**/



	/**
	 * Returns encoded message.
	 * @return string
	 */
	public function generateMessage()
	{
		$output = '';
		$boundary = '--------' . md5(uniqid('', TRUE));

		foreach ($this->headers as $name => $value) {
			if ($this->parts && $name === 'Content-Type') {
				$value .= ';' . self::EOL . "\tboundary=\"$boundary\"";
			}
			$output .= $name . ': ' . self::encodeQuotedPrintableHeader($value) . self::EOL;
		}
		$output .= self::EOL;

		$body = (string) $this->body;
		if ($body !== '') {
			switch ($this->getEncoding()) {
			case self::ENCODING_QUOTED_PRINTABLE:
				$output .= self::encodeQuotedPrintable($body);
				break;

			case self::ENCODING_BASE64:
				$output .= rtrim(chunk_split(base64_encode($body), self::LINE_LENGTH, self::EOL));
				break;

			case self::ENCODING_7BIT:
				$output .= preg_replace('#[\x80-\xFF]+#', '', $body);
				break;

			case self::ENCODING_8BIT:
				$output .= $body;
				break;

			default:
				throw new /*\*/InvalidStateException('Unknown encoding');
			}
		}

		if ($this->parts) {
			if (substr($output, -strlen(self::EOL)) !== self::EOL) $output .= self::EOL;
			foreach ($this->parts as $part) {
				$output .= '--' . $boundary . self::EOL . $part->generateMessage() . self::EOL;
			}
			$output .= '--' . $boundary.'--';
		}

		return $output;
	}



	/**
	 * Converts a 8 bit header to a quoted-printable string.
	 * @parram string
	 * @parram string
	 * @return string
	 */
	public static function encodeQuotedPrintableHeader($s, $charset = 'UTF-8')
	{
		if (is_array($s)) $s = implode(',', $s);

		// \x20-\x7F without \x3D \x3F \x20 \x5F
		$range = "\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2a\x2b\x2c\x2d\x2e\x2f\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3a\x3b\x3c\x3e"
			. "\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4a\x4b\x4c\x4d\x4e\x4f\x50\x51\x52\x53\x54\x55\x56\x57\x58\x59\x5a\x5b\x5c\x5d\x5e"
			. "\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6a\x6b\x6c\x6d\x6e\x6f\x70\x71\x72\x73\x74\x75\x76\x77\x78\x79\x7a\x7b\x7c\x7d\x7e";

		if (strspn($s, $range . "=? _\r\n\t") === strlen($s)) {
			return $s;
		}

		$prefix = "=?$charset?Q?";
		$pos = 0;
		$len = 0;
		$o = '';
		$inside = FALSE;
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} elseif ($s[$pos] === ' ') {
				$o .= $tmp = $inside ? '=20?=' : ' ';
				$len += strlen($tmp);
				if ($inside && $len > self::LINE_LENGTH) {
					$o .= self::EOL . "\t";
					$len = 0;
				}
				$inside = FALSE;
				$pos++;

			} else {
				if (!$inside) {
					$inside = TRUE;
					$o .= $prefix;
					$len += strlen($prefix);
				}

				$o .= '=' . strtoupper(bin2hex($s[$pos]));
				$len += 3;
				$pos++;
			}
		}
		return $o . ($inside ? '?=' : '');
	}



	/**
	 * Converts a 8 bit string to a quoted-printable string.
	 * @parram string
	 * @return string
	 */
	public static function encodeQuotedPrintable($s)
	{
		// \x20-\x7F without \x3D
		$range = "\x20\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2a\x2b\x2c\x2d\x2e\x2f\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3a\x3b\x3c\x3e\x3f"
			. "\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4a\x4b\x4c\x4d\x4e\x4f\x50\x51\x52\x53\x54\x55\x56\x57\x58\x59\x5a\x5b\x5c\x5d\x5e\x5f"
			. "\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6a\x6b\x6c\x6d\x6e\x6f\x70\x71\x72\x73\x74\x75\x76\x77\x78\x79\x7a\x7b\x7c\x7d\x7e";

		$pos = 0;
		$len = 0;
		$o = '';
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				while ($len + $l > self::LINE_LENGTH) {
					$o .= substr($s, $pos, self::LINE_LENGTH - $len) . '=' . self::EOL;
					$pos += self::LINE_LENGTH - $len;
					$l -= self::LINE_LENGTH - $len;
					$len = 0;
				}
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} else {
				$len += 3;
				if ($len > self::LINE_LENGTH) {
					$o .= '=' . self::EOL;
					$len = 3;
				}
				$o .= '=' . strtoupper(bin2hex($s[$pos]));
				$pos++;
			}
		}
		return rtrim($o, '=' . self::EOL);
	}

}
