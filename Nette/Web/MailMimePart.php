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



require_once dirname(__FILE__) . '/../Object.php';



/**
 * MIME message part.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Web
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
	 * Sets an user header.
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 */
	public function setHeader($name, $value, $append = FALSE)
	{
		if (!$name || preg_match('#[^a-z0-9-]#i', $name)) {
			throw new /*\*/InvalidArgumentException("Header must be non-empty alphanumeric string, '$name' given.");
		}

		$value = preg_replace('#[\r\n\t]#', '', $value);
		if ($value == '') { // intentionally ==
			unset($this->headers[$name]);

		} elseif ($append) {
			$this->headers[$name][] = $value;

		} else {
			$this->headers[$name] = $value;
		}
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
	 * @return void
	 */
	public function setContentType($contentType, $charset = NULL)
	{
		$this->setHeader('Content-Type', $contentType . ($charset ? "; charset=$charset" : ''));
	}



	/**
	 * Sets Content-Transfer-Encoding header.
	 * @param  string
	 * @return void
	 */
	public function setEncoding($encoding)
	{
		$this->setHeader('Content-Transfer-Encoding', $encoding);
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
	 * @param  string
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
	 * Returns encoded message.
	 * @return string
	 */
	public function generateMessage()
	{
		$output = '';
		foreach ($this->headers as $name => $value) {
			if ($this->parts && $name === 'Content-Type') {
				$boundary = '=_' . md5(uniqid('', TRUE));
				$value .= ';' . self::EOL . "\tboundary=\"$boundary\"";
			}
			$output .= $name . ': ' . self::encodeQuotedPrintableHeader($value) . self::EOL;
		}
		$output .= self::EOL;

		if ($this->body !== '') {
			switch ($this->getEncoding()) {
			case self::ENCODING_QUOTED_PRINTABLE:
				$output .= self::encodeQuotedPrintable($this->body);
				break;

			case self::ENCODING_BASE64:
				$output .= rtrim(chunk_split(base64_encode($this->body), 76, self::EOL));
				break;

			case self::ENCODING_7BIT:
			case self::ENCODING_8BIT:
				$output .= $this->body;
				break;

			default:
				throw new /*\*/InvalidStateException('Unknown encoding');
			}
		}

		if ($this->parts) {
			if (substr($output, -1) !== self::EOL) $output .= self::EOL;
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

		if (strspn($s, $range . "=? _\n\t") === strlen($s)) {
			return $s;
		}

		$prefix = "=?$charset?Q?";
		$maxLen = 74 - strlen($prefix);
		$pos = 0;
		$len = 0;
		$o = '';
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} else {
				$len += 3;
				if ($len > $maxLen && $s[$pos] === ' ') {
					$o .= "?=\n $prefix";
					$len = 3;
				}
				$o .= '=' . bin2hex($s[$pos]);
				$pos++;
			}
		}
		return $prefix . $o . '?=';
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

		$maxLen = 74;
		$pos = 0;
		$len = 0;
		$o = '';
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				while ($len + $l > $maxLen) {
					$o .= substr($s, $pos, $maxLen - $len) . "=\n";
					$pos += $maxLen - $len;
					$l -= $maxLen - $len;
					$len = 0;
				}
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} else {
				$len += 3;
				if ($len > $maxLen) {
					$o .= "=\n";
					$len = 3;
				}
				$o .= '=' . bin2hex($s[$pos]);
				$pos++;
			}
		}
		return rtrim($o, "=\n");
	}

}
