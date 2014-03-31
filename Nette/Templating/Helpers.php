<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Templating;

use Nette;


/**
 * @deprecated
 */
class Helpers extends Nette\Latte\Runtime\Filters
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
		'reverse' =>  'Nette\Utils\Strings::reverse',
		'replacere' => 'Nette\Utils\Strings::replace',
		'url' => 'rawurlencode',
		'escapeurl' => 'rawurlencode',
		'striptags' => 'strip_tags',
		'substr' => 'Nette\Utils\Strings::substring',
		'repeat' => 'str_repeat',
		'implode' => 'implode',
		'number' => 'number_format',
	);


	/**
	 * Try to load the requested helper.
	 * @param  string  helper name
	 * @return callable
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return array(__CLASS__, $helper);
		} elseif (isset(self::$helpers[$helper])) {
			return self::$helpers[$helper];
		}
	}


	/**
	 * Date/time modification.
	 * @param  string|int|DateTime
	 * @param  string|int
	 * @param  string
	 * @return Nette\Utils\DateTime
	 */
	public static function modifyDate($time, $delta, $unit = NULL)
	{
		return $time == NULL // intentionally ==
			? NULL
			: Nette\Utils\DateTime::from($time)->modify($delta . $unit);
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
	 * /dev/null.
	 * @param  mixed
	 * @return string
	 */
	public static function null()
	{
		return '';
	}


	public static function optimizePhp($source, $lineLength = 80)
	{
		return Nette\Latte\Helpers::optimizePhp($source, $lineLength);
	}

}
