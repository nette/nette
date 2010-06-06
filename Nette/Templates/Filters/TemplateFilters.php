<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Templates
 */

namespace Nette\Templates;

use Nette;



/**
 * Standard template compile-time filters shipped with Nette Framework.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Templates
 */
final class TemplateFilters
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* Filter removePhp ****************d*g**/



	/**
	 * Filters out PHP code.
	 * @param  string
	 * @return string
	 */
	public static function removePhp($s)
	{
		return preg_replace('#\x01@php:p\d+@\x02#', '<?php ?>', $s); // Template hides PHP code in these snippets
	}



	/********************* Filter relativeLinks ****************d*g**/



	/**
	 * Filter relativeLinks: prepends root to relative links.
	 * @param  string
	 * @return string
	 */
	public static function relativeLinks($s)
	{
		return preg_replace(
			'#(src|href|action)\s*=\s*(["\'])(?![a-z]+:|[\x01/\\#])#', // \x01 is PHP snippet
			'$1=$2<?php echo \\$baseUri ?>',
			$s
		);
	}



	/********************* Filter netteLinks ****************d*g**/



	/**
	 * Filter netteLinks: translates links "nette:...".
	 *   nette:destination?arg
	 * @param  string
	 * @return string
	 */
	public static function netteLinks($s)
	{
		return preg_replace_callback(
			'#(src|href|action)\s*=\s*(["\'])(nette:.*?)([\#"\'])#',
			array(__CLASS__, 'netteLinksCb'),
			$s
		);
	}



	/**
	 * Callback for self::netteLinks.
	 * Parses a "nette" URI (scheme is 'nette') and converts to real URI
	 */
	private static function netteLinksCb($m)
	{
		list(, $attr, $quote, $uri, $fragment) = $m;

		$parts = parse_url($uri);
		if (isset($parts['scheme']) && $parts['scheme'] === 'nette') {
			return $attr . '=' . $quote . '<?php echo $template->escape($control->'
				. "link('"
				. (isset($parts['path']) ? $parts['path'] : 'this!')
				. (isset($parts['query']) ? '?' . $parts['query'] : '')
				. '\'))?>'
				. $fragment;
		} else {
			return $m[0];
		}
	}



	/********************* Filter texyElements ****************d*g**/



	/** @var Texy */
	public static $texy;



	/**
	 * Process <texy>...</texy> elements.
	 * @param  string
	 * @return string
	 */
	public static function texyElements($s)
	{
		return preg_replace_callback(
			'#<texy([^>]*)>(.*?)</texy>#s',
			array(__CLASS__, 'texyCb'),
			$s
		);
	}



	/**
	 * Callback for self::texyBlocks.
	 */
	private static function texyCb($m)
	{
		list(, $mAttrs, $mContent) = $m;

		// parse attributes
		$attrs = array();
		if ($mAttrs) {
			preg_match_all(
				'#([a-z0-9:-]+)\s*(?:=\s*(\'[^\']*\'|"[^"]*"|[^\'"\s]+))?()#isu',
				$mAttrs,
				$arr,
				PREG_SET_ORDER
			);

			foreach ($arr as $m) {
				$key = strtolower($m[1]);
				$val = $m[2];
				if ($val == NULL) $attrs[$key] = TRUE;
				elseif ($val{0} === '\'' || $val{0} === '"') $attrs[$key] = html_entity_decode(substr($val, 1, -1), ENT_QUOTES, 'UTF-8');
				else $attrs[$key] = html_entity_decode($val, ENT_QUOTES, 'UTF-8');
			}
		}

		return self::$texy->process($m[2]);
	}

}
