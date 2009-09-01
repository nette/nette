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
	const LINE_LENGTH = 76;

	/** @var array */
	private $headers = array();

	/** @var array */
	private $parts = array();

	/** @var string */
	private $body;



	/**
	 * Sets a header.
	 * @param  string
	 * @param  string|array  value or pair email => name
	 * @param  bool
	 * @return MailMimePart  provides a fluent interface
	 */
	public function setHeader($name, $value, $append = FALSE)
	{
		if (!$name || preg_match('#[^a-z0-9-]#i', $name)) {
			throw new /*\*/InvalidArgumentException("Header name must be non-empty alphanumeric string, '$name' given.");
		}

		if ($value == NULL) { // intentionally ==
			if (!$append) {
				unset($this->headers[$name]);
			}

		} elseif (is_array($value)) { // email
			$tmp = & $this->headers[$name];
			if (!$append || !is_array($tmp)) {
				$tmp = array();
			}

			foreach ($value as $email => $name) {
				if (!preg_match('#^[^@",\s]+@[^@",\s]+\.[a-z]{2,10}$#i', $email)) {
					throw new /*\*/InvalidArgumentException("Email address '$email' is not valid.");
				}

				if (preg_match('#[\r\n]#', $name)) {
					throw new /*\*/InvalidArgumentException("Name cannot contain the line separator.");
				}
				$tmp[$email] = $name;
			}

		} else {
			$this->headers[$name] = preg_replace('#[\r\n]+#', ' ', $value);
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
	 * Removes a header.
	 * @param  string
	 * @return MailMimePart  provides a fluent interface
	 */
	public function clearHeader($name)
	{
		unset($this->headers[$name]);
		return $this;
	}



	/**
	 * Returns an encoded header.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function getEncodedHeader($name, $charset = 'UTF-8')
	{
		$len = strlen($name) + 2;

		if (!isset($this->headers[$name])) {
			return NULL;

		} elseif (is_array($this->headers[$name])) {
			$s = '';
			foreach ($this->headers[$name] as $email => $name) {
				if ($name != NULL) { // intentionally ==
					$s .= self::encodeQuotedPrintableHeader(
						strspn($name, '.,;<@>()[]"=?') ? '"' . addcslashes($name, '"\\') . '"' : $name,
						$charset, $len
					);
					$email = " <$email>";
				}
				if ($len + strlen($email) + 1 > self::LINE_LENGTH) {
					$s .= self::EOL . "\t";
					$len = 1;
				}
				$s .= "$email,";
				$len += strlen($email) + 1;
			}
			return substr($s, 0, -1);

		} else {
			return self::encodeQuotedPrintableHeader($this->headers[$name], $charset, $len);
		}
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
			$output .= $name . ': ' . $this->getEncodedHeader($name);
			if ($this->parts && $name === 'Content-Type') {
				$output .= ';' . self::EOL . "\tboundary=\"$boundary\"";
			}
			$output .= self::EOL;
		}
		$output .= self::EOL;

		$body = (string) $this->body;
		if ($body !== '') {
			switch ($this->getEncoding()) {
			case self::ENCODING_QUOTED_PRINTABLE:
				$output .= function_exists('quoted_printable_encode') ? quoted_printable_encode($body) : self::encodeQuotedPrintable($body);
				break;

			case self::ENCODING_BASE64:
				$output .= rtrim(chunk_split(base64_encode($body), self::LINE_LENGTH, self::EOL));
				break;

			case self::ENCODING_7BIT:
				$body = preg_replace('#[\x80-\xFF]+#', '', $body);
				// break intentionally omitted

			case self::ENCODING_8BIT:
				$body = str_replace(array("\x00", "\r"), '', $body);
				$body = str_replace("\n", self::EOL, $body);
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



	/********************* QuotedPrintable helpers ****************d*g**/



	/**
	 * Converts a 8 bit header to a quoted-printable string.
	 * @param  string
	 * @param  string
	 * @param  int
	 * @return string
	 */
	private static function encodeQuotedPrintableHeader($s, $charset = 'UTF-8', & $len = 0)
	{
		$range = '!"#$%&\'()*+,-./0123456789:;<>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^`abcdefghijklmnopqrstuvwxyz{|}'; // \x21-\x7E without \x3D \x3F \x5F

		if (strspn($s, $range . "=? _\r\n\t") === strlen($s)) {
			return $s;
		}

		$prefix = "=?$charset?Q?";
		$pos = 0;
		$len += strlen($prefix);
		$o = $prefix;
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				while ($len + $l > self::LINE_LENGTH - 2) { // 2 = length of suffix ?=
					$lx = self::LINE_LENGTH - $len - 2;
					$o .= substr($s, $pos, $lx) . '?=' . self::EOL . "\t" . $prefix;
					$pos += $lx;
					$l -= $lx;
					$len = strlen($prefix) + 1;
				}
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} else {
				$len += 3;
				// \xC0 tests UTF-8 character boudnary; 9 is reserved space for 4bytes UTF-8 character
				if (($s[$pos] & "\xC0") !== "\x80" && $len > self::LINE_LENGTH - 2 - 9) {
					$o .= '?=' . self::EOL . "\t" . $prefix;
					$len = strlen($prefix) + 1 + 3;
				}
				$o .= '=' . strtoupper(bin2hex($s[$pos]));
				$pos++;
			}
		}
		return $o . '?=';
	}



	/**
	 * Converts a 8 bit string to a quoted-printable string.
	 * @param  string
	 * @return string
	 */
	public static function encodeQuotedPrintable($s)
	{
		$range = '!"#$%&\'()*+,-./0123456789:;<>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}'; // \x21-\x7E without \x3D
		$pos = 0;
		$len = 0;
		$o = '';
		$size = strlen($s);
		while ($pos < $size) {
			if ($l = strspn($s, $range, $pos)) {
				while ($len + $l > self::LINE_LENGTH - 1) { // 1 = length of suffix =
					$lx = self::LINE_LENGTH - $len - 1;
					$o .= substr($s, $pos, $lx) . '=' . self::EOL;
					$pos += $lx;
					$l -= $lx;
					$len = 0;
				}
				$o .= substr($s, $pos, $l);
				$len += $l;
				$pos += $l;

			} else {
				$len += 3;
				if ($len > self::LINE_LENGTH - 1) {
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
