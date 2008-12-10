<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/



/**
 * Standard template helpers shipped with Nette Framework.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Templates
 */
final class TemplateHelpers
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Escapes string for use inside HTML template.
	 * @param  string
	 * @return string
	 */
	public static function escapeHtml($s)
	{
		if (is_string($s)) {
			return htmlSpecialChars($s, ENT_QUOTES);
		}
		return $s;
	}



	/**
	 * Escapes string for use inside CSS template.
	 * @param  string
	 * @return string
	 */
	public static function escapeCss($s)
	{
		if (is_string($s)) {
			// http://www.w3.org/TR/2006/WD-CSS21-20060411/syndata.html#q6
			return addcslashes($s, "\x00..\x2C./:;<=>?@[\\]^`{|}~");
		}
		return $s;
	}



	/**
	 * Convert to lower case.
	 * @param  string
	 * @return string
	 */
	public static function lower($s)
	{
		return mb_strtolower($s, 'UTF-8');
	}



	/**
	 * Convert to upper case.
	 * @param  string
	 * @return string
	 */
	public static function upper($s)
	{
		return mb_strtoupper($s, 'UTF-8');
	}



	/**
	 * Capitalize string.
	 * @param  string
	 * @return string
	 */
	public static function capitalize($s)
	{
		return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
	}



	/**
	 * Replaces all repeated white spaces with a single space.
	 * @param  string
	 * @return string
	 */
	public static function strip($s)
	{
		return preg_replace('#\\s+#', ' ', $s);
	}



	/**
	 * Date/time formatting.
	 * @param  string|int|DateTime
	 * @param  string
	 * @return string
	 */
	public static function date($value, $format = "%x")
	{
		$value = is_numeric($value) ? (int) $value : ($value instanceof DateTime ? $value->format('U') : strtotime($value));
		return strftime($format, $value);
	}

}