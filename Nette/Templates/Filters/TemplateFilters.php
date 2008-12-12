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
 * Standard template filters shipped with Nette Framework.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Templates
 */
final class TemplateFilters
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/********************* Filter phpEvaluation ****************d*g**/



	/**
	 * Template filter PHP (Evaluates template in limited scope).
	 * @param  Template
	 * @param  string (hidden)
	 * @return string
	 */
	public static function phpEvaluation(Template $template/*, $content, $isFile*/)
	{
		extract($template->getParams(), EXTR_SKIP); // skip $this & $template
		if (func_num_args() > 2 && func_get_arg(2)) {
			include func_get_arg(1);
		} else {
			eval('?>' . func_get_arg(1));
		}
	}



	/********************* Filter curlyBrackets ****************d*g**/


	/** @deprecated */
	public static $curlyXlatMask;



	/** @deprecated */
	public static function curlyBrackets($template, $s)
	{
		trigger_error('Deprecated: use $template->registerFilter(\'CurlyBracketsFilter::invoke\') instead.', E_USER_WARNING);
		return CurlyBracketsFilter::invoke($template, $s);
	}



	/********************* Filter fragments ****************d*g**/



	/**
	 * Template with defined fragments (experimental).
	 *    <nette:fragment id="main"> ... </nette:fragment>
	 *
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function fragments(Template $template, $s)
	{
		$file = $template->getFile();
		$a = strpos($file, '#');
		if ($a === FALSE) {
			return $s;
		}
		$fragment = substr($file, $a + 1);
		if (preg_match('#<nette:fragment\s+id="' . $fragment . '">(.*)</nette:fragment>#sU', $s, $matches)) {
			return $matches[1];

		} else {
			trigger_error("Fragment '$file' is not defined.", E_USER_WARNING);
			return '';
		}
	}



	/********************* Filter removePhp ****************d*g**/



	/**
	 * Filters out PHP code.
	 *
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function removePhp(Template $template, $s)
	{
		$res = '';
		foreach (token_get_all($s) as $token) {
			if (is_array($token) && $token[0] === T_INLINE_HTML) {
				$res .= $token[1] . '<?php ?>';
			}
		}
		return $res;
	}



	/********************* Filter autoConfig ****************d*g**/



	/**
	 * Template with configuration (experimental).
	 *    <?nette filter="CurlyBracketsFilter::invoke"?>
	 *
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function autoConfig(Template $template, $s)
	{
		throw new /*\*/NotImplementedException;
		preg_match_all('#<\\?nette(.*)\\?>#sU', $s, $matches, PREG_SET_ORDER);
		foreach ($matches as $m) {
		}
		return preg_replace('#<\\?nette(.*)\\?>#sU', '', $s);
	}



	/********************* Filter relativeLinks ****************d*g**/



	/**
	 * Filter relativeLinks: prepends root to relative links.
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function relativeLinks($template, $s)
	{
		return preg_replace(
			'#(src|href|action)\s*=\s*["\'](?![a-z]+:|/|<|\\#)#',
			'$1="' . $template->baseUri,
			$s
		);
	}



	/********************* Filter netteLinks ****************d*g**/



	/**
	 * Filter netteLinks: translates links "nette:...".
	 *   nette:destination?arg
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function netteLinks($template, $s)
	{
		return preg_replace_callback(
			'#(src|href|action|on[a-z]+)\s*=\s*"(nette:.*?)([\#"])#',
			array(__CLASS__, 'tnlCb'),
			$s)
		;
	}



	/**
	 * Callback for self::netteLinks.
	 * Parses a "nette" URI (scheme is 'nette') and converts to real URI
	 */
	private static function tnlCb($m)
	{
		list(, $attr, $uri, $fragment) = $m;

		$parts = parse_url($uri);
		if (isset($parts['scheme']) && $parts['scheme'] === 'nette') {
			return $attr . '="<?php echo $template->escape($control->'
				. (strncmp($attr, 'on', 2) ? 'link' : 'ajaxLink')
				. '(\''
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
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function texyElements($template, $s)
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


// back compatiblity:
TemplateFilters::$curlyXlatMask = & CurlyBracketsFilter::$statements;
