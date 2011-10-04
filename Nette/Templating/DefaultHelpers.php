<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Templating;

use Nette,
	Nette\Utils\Strings,
	Nette\Forms\Form,
	Nette\Utils\Html;



/**
 * Standard template run-time helpers shipped with Nette Framework (http://nette.org)
 *
 * @author     David Grudl
 */
final class DefaultHelpers
{
	private static $helpers = array(
		'normalize' => 'Nette\Utils\Strings::normalize',
		'toascii' => 'Nette\Utils\Strings::toAscii',
		'webalize' => 'Nette\Utils\Strings::webalize',
		'truncate' => 'Nette\Utils\Strings::truncate',
		'lower' => 'Nette\Utils\Strings::lower',
		'upper' => 'Nette\Utils\Strings::upper',
		'firstupper' => 'Nette\Utils\Strings::firstUpper',
		'capitalize' => 'Nette\Utils\Strings::capitalize',
		'trim' => 'Nette\Utils\Strings::trim',
		'padleft' => 'Nette\Utils\Strings::padLeft',
		'padright' => 'Nette\Utils\Strings::padRight',
		'replacere' => 'Nette\Utils\Strings::replace',
		'url' => 'rawurlencode',
		'striptags' => 'strip_tags',
		'nl2br' => 'nl2br',
		'substr' => 'Nette\Utils\Strings::substring',
		'repeat' => 'str_repeat',
		'implode' => 'implode',
		'number' => 'number_format',
	);

	/** @var string default date format */
	public static $dateFormat = '%x';



	/**
	 * Try to load the requested helper.
	 * @param  string  helper name
	 * @return callback
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return callback(__CLASS__, $helper);
		} elseif (isset(self::$helpers[$helper])) {
			return self::$helpers[$helper];
		}
	}



	/**
	 * Escapes string for use inside HTML template.
	 * @param  mixed  UTF-8 encoding or 8-bit
	 * @param  int    optional attribute quotes
	 * @return string
	 */
	public static function escapeHtml($s, $quotes = ENT_QUOTES)
	{
		if (is_object($s) && ($s instanceof ITemplate || $s instanceof Html || $s instanceof Form)) {
			return $s->__toString(TRUE);
		}
		return htmlSpecialChars($s, $quotes);
	}



	/**
	 * Escapes string for use inside HTML comments.
	 * @param  mixed  UTF-8 encoding or 8-bit
	 * @return string
	 */
	public static function escapeHtmlComment($s)
	{
		// -- has special meaning in different browsers
		return str_replace('--', '--><!-- ', $s); // HTML tags have no meaning inside comments
	}



	/**
	 * Escapes string for use inside XML 1.0 template.
	 * @param  string UTF-8 encoding or 8-bit
	 * @return string
	 */
	public static function escapeXML($s)
	{
		// XML 1.0: \x09 \x0A \x0D and C1 allowed directly, C0 forbidden
		// XML 1.1: \x00 forbidden directly and as a character reference,
		//   \x09 \x0A \x0D \x85 allowed directly, C0, C1 and \x7F allowed as character references
		return htmlSpecialChars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '', $s), ENT_QUOTES);
	}



	/**
	 * Escapes string for use inside CSS template.
	 * @param  string UTF-8 encoding or 8-bit
	 * @return string
	 */
	public static function escapeCss($s)
	{
		// http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
		return addcslashes($s, "\x00..\x1F!\"#$%&'()*+,./:;<=>?@[\\]^`{|}~");
	}



	/**
	 * Escapes string for use inside JavaScript template.
	 * @param  mixed  UTF-8 encoding
	 * @return string
	 */
	public static function escapeJs($s)
	{
		if (is_object($s) && ($s instanceof ITemplate || $s instanceof Html || $s instanceof Form)) {
			$s = $s->__toString(TRUE);
		}
		return str_replace(']]>', ']]\x3E', Nette\Utils\Json::encode($s));
	}



	/**
	 * Escapes string for use inside iCal template.
	 * @param  mixed  UTF-8 encoding
	 * @return string
	 */
	public static function escapeICal($s)
	{
		// http://www.ietf.org/rfc/rfc5545.txt
		return addcslashes(preg_replace('#[\x00-\x08\x0B\x0C-\x1F]+#', '', $s), "\";\\,:\n");
	}



	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param  string UTF-8 encoding or 8-bit
	 * @return string
	 */
	public static function strip($s)
	{
		return Strings::replace(
			$s,
			'#(</textarea|</pre|</script|^).*?(?=<textarea|<pre|<script|$)#si',
			/*5.2* callback(*/function($m) {
				return trim(preg_replace("#[ \t\r\n]+#", " ", $m[0]));
			}/*5.2* )*/);
	}



	/**
	 * Indents the HTML content from the left.
	 * @param  string UTF-8 encoding or 8-bit
	 * @param  int
	 * @param  string
	 * @return string
	 */
	public static function indent($s, $level = 1, $chars = "\t")
	{
		if ($level >= 1) {
			$s = Strings::replace($s, '#<(textarea|pre).*?</\\1#si', /*5.2* callback(*/function($m) {
				return strtr($m[0], " \t\r\n", "\x1F\x1E\x1D\x1A");
			}/*5.2* )*/);
			$s = Strings::indent($s, $level, $chars);
			$s = strtr($s, "\x1F\x1E\x1D\x1A", " \t\r\n");
		}
		return $s;
	}



	/**
	 * Date/time formatting.
	 * @param  string|int|DateTime
	 * @param  string
	 * @return string
	 */
	public static function date($time, $format = NULL)
	{
		if ($time == NULL) { // intentionally ==
			return NULL;
		}

		if (!isset($format)) {
			$format = self::$dateFormat;
		}

		$time = Nette\DateTime::from($time);
		return strpos($format, '%') === FALSE
			? $time->format($format) // formats using date()
			: strftime($format, $time->format('U')); // formats according to locales
	}



	/**
	 * Converts to human readable file size.
	 * @param  int
	 * @param  int
	 * @return string
	 */
	public static function bytes($bytes, $precision = 2)
	{
		$bytes = round($bytes);
		$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		foreach ($units as $unit) {
			if (abs($bytes) < 1024 || $unit === end($units)) {
				break;
			}
			$bytes = $bytes / 1024;
		}
		return round($bytes, $precision) . ' ' . $unit;
	}



	/**
	 * Returns array of string length.
	 * @param  mixed
	 * @return int
	 */
	public static function length($var)
	{
		return is_string($var) ? Strings::length($var) : count($var);
	}



	/**
	 * Performs a search and replace.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function replace($subject, $search, $replacement = '')
	{
		return str_replace($search, $replacement, $subject);
	}



	/**
	 * The data: URI generator.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function dataStream($data, $type = NULL)
	{
		if ($type === NULL) {
			$type = Nette\Utils\MimeTypeDetector::fromString($data, NULL);
		}
		return 'data:' . ($type ? "$type;" : '') . 'base64,' . base64_encode($data);
	}



	/**
	 * /dev/null.
	 * @param  mixed
	 * @return string
	 */
	public static function null($value)
	{
		return '';
	}

}
