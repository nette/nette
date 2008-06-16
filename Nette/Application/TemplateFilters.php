<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com/
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com/
 * @category   Nette
 * @package    Nette::Application
 */

/*namespace Nette::Application;*/



/**
 * Filters for Template.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Application
 * @version    $Revision$ $Date$
 */
final class TemplateFilters
{
	/** @var Texy */
	public static $texy;

	/** @var Template */
	private static $template;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Filter curlyBrackets: Support for {...} in template.
	 *   {$variable} with escaping
	 *   {!variable} without escaping
	 *   {~variable} with translation
	 *   {*comment*} will be removed
	 *   {=expression} evaluate with escaping
	 *   {!=expression} evaluate without escaping
	 *   {~=expression} evaluate with translation
	 *   {%var%} environment variable with escaping (to be discussed)
	 *   {if ?} ... {elseif ?} ... {else} ... {/if}
	 *   {for ?} ... {/for}
	 *   {foreach ?} ... {/foreach}
	 *   {include ?}
	 *
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function curlyBrackets($template, $s)
	{
		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}#s', '', $s);

		// simple replace
		$s = str_replace(
			array('{else}', '{/if}', '{/foreach}', '{/for}'), // or <%else%>, <%/if%>, <%/foreach%> ?
			array('<?php else:?>', '<?php endif?>', '<?php endforeach?>', '<?php endfor?>'),
			$s
		);

		// smarter replace
		$s = preg_replace_callback(
			'#\\{(if |elseif |foreach |for |include |\\$|!=|~=|=|!|~)([^}]+?)\\}#s',
			array(__CLASS__, 'curlyCb'),
			$s
		);

		//$s = preg_replace('#<?(php|=|)(.*)? >#s', '', $s);  // PHP
		return $s;
	}



	/** @var array */
	public static $curlyXlat = array(
		'$' => '<?php echo $template->escape($#)?>',
		'!' => '<?php echo $#?>',
		'~' => '<?php echo $template->translate($#)?>',
		'=' => '<?php echo $template->escape(#)?>',
		'!=' => '<?php echo #?>',
		'~=' => '<?php echo $template->translate(#)?>',
		'%' => '<?php echo $template->escape(Environment::getVariable(\'#\'))?>',
		'if ' => '<?php if (#):?>',
		/*'ifset ' => '<?php if (!empty(#)):?>',*/
		'elseif ' => '<?php elseif (#):?>',
		/*'elseifset ' => '<?php elseif (!empty(#)):?>',*/
		'foreach ' => '<?php foreach (#):?>',
		'for ' => '<?php for (#):?>',
		'include ' => '<?php $template->render(#)?>',
	);



	/**
	 * Callback for self::curlyBrackets.
	 */
	private static function curlyCb($m)
	{
		list(, $mod, $var) = $m;
		// if ($mod === '%') $var = rtrim($var, '%');
		return str_replace('#', $var, self::$curlyXlat[$mod]);
	}



	/**
	 * Filter relativeLinks: prepends root to relative links.
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function relativeLinks($template, $s)
	{
		return preg_replace(
			'#(src|href|action)\s*=\s*"(?![a-z]+:|/|<)#',
			'$1="' . /*Nette::*/Environment::getHttpRequest()->getUri()->basePath,
			$s
		);
	}



	/**
	 * Filter netteLinks: translates links "nette:...".
	 *   nette:view?arg
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function netteLinks($template, $s)
	{
		return preg_replace_callback(
			'#(src|href|action|onclick)\s*=\s*"(nette:.*?)([\#"])#',
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
		if (!isset($parts['scheme']) || $parts['scheme'] !== 'nette') return $m[0];

		if (isset($parts['query'])) {
			parse_str($parts['query'], $params); // vyzaduje vypnute fuckingQuotes
			foreach ($params as $k => $v) {
				if ($v === '') $params[$k] = NULL;
			}
		} else {
			$params = array();
		}
		$destination = isset($parts['path']) ? $parts['path'] : Presenter::THIS_VIEW;

		return $attr . '="<?php echo $template->escape($component->link('
			. var_export($destination, TRUE) . ', ' . var_export($params, TRUE)
			. '))?>' . $fragment;
	}



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