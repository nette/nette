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

/*use Nette\Caching\Cache;*/



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



	/**
	 * Filter curlyBrackets: Support for {...} in template.
	 *   {$variable} with escaping
	 *   {!$variable} without escaping
	 *   {*comment*} will be removed
	 *   {=expression} echo with escaping
	 *   {!=expression} echo without escaping
	 *   {?expression} evaluate PHP statement
	 *   {_expression} echo with escaping and translation
	 *   {link destination ...} control link
	 *   {plink destination ...} presenter link
	 *   {ajaxlink destination ...} ajax link
	 *   {if ?} ... {elseif ?} ... {else} ... {/if} // or <%else%>, <%/if%>, <%/foreach%> ?
	 *   {for ?} ... {/for}
	 *   {foreach ?} ... {/foreach}
	 *   {include ?}
	 *   {cache ?} ... {/cache} cached block
	 *   {snippet ?} ... {/snippet ?} control snippet
	 *   {block|texy} ... {/block} capture of filter block
	 *   {contentType ...} HTTP Content-Type header
	 *   {debugbreak}
	 *
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function curlyBrackets($template, $s)
	{
		// snippets support
		if (isset($template->control) &&
			$template->control instanceof /*Nette\Application\*/IPartiallyRenderable) {
			$s = '<?php if ($control->isOutputAllowed()) { ?>' . $s . '<?php } ?>';
		}
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if ($control->isOutputAllowed()) { ?>',
			$s
		);

		// cache support
		$s = '<?php TemplateFilters::$curlyCacheFrames[0][Cache::FILES][] = $template->getFile(); ?>' . $s;

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		self::$curlyBlocks = array();
		$k = array();
		foreach (self::$curlyXlatMask as $key => $foo)
		{
			$key = preg_quote($key, '#');
			if (preg_match('#[a-zA-Z0-9]$#', $key)) {
				$key .= '(?=[|}\s])';
			}
			$k[] = $key;
		}
		$s = preg_replace_callback(
			'#\\{(' . implode('|', $k) . ')([^}]*?)(?:\\|([a-z](?:[^\'"}\s|]+|\\|[a-z]|\'[^\']*\'|"[^"]*")*))?\\}()#s',
			array(__CLASS__, 'curlyCb'),
			$s
		);

		return $s;
	}



	/** @var array */
	public static $curlyXlatMask = array(
		'block' => '<?php ob_start(); try { ?>',
		'/block' => '<?php } catch (Exception $_e) { ob_end_clean(); throw $_e; } # ?>',
		'snippet' => '<?php } if ($control->beginSnippet(#)) { ?>',
		'/snippet' => '<?php $control->endSnippet(#); } if ($control->isOutputAllowed()) { ?>',
		'cache' => '<?php TemplateFilters::$curlyCacheFrames[0][Cache::ITEMS][] = #; $_cache = Environment::getCache("Nette.Template.Curly"); if (isset($_cache[#])) { echo $_cache[#]; } else { ob_start(); TemplateFilters::curlyAddFrame(##); try { ?>',
		'/cache' => '<?php $_cache->save(#); } catch (Exception $_e) { ob_end_clean(); throw $_e; } } ?>',
		'if' => '<?php if (#): ?>',
		'elseif' => '<?php elseif (#): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'foreach' => '<?php foreach (#): ?>',
		'/foreach' => '<?php endforeach ?>',
		'for' => '<?php for (#): ?>',
		'/for' => '<?php endfor ?>',
		'while' => '<?php while (#): ?>',
		'/while' => '<?php endwhile ?>',
		'include' => '<?php $template->subTemplate(#)->render() ?>',
		'ajaxlink' => '<?php echo $template->escape(#) ?>',
		'plink' => '<?php echo $template->escape(#) ?>',
		'link' => '<?php echo $template->escape(#) ?>',
		'ifCurrent' => '<?php #if ($presenter->getCreatedRequest() && $presenter->getCreatedRequest()->hasFlag("current")): ?>',
		'contentType' => '<?php Environment::getHttpResponse()->setHeader("Content-Type", "#") ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',
		'!=' => '<?php echo # ?>',
		'_' => '<?php echo $template->escape($template->translate(#)) ?>',
		'=' => '<?php echo $template->escape(#) ?>',
		'!$' => '<?php echo # ?>',
		'!' => '<?php echo # ?>',
		'$' => '<?php echo $template->escape(#) ?>',
		'?' => '<?php # ?>',
	);

	/** @var array */
	public static $curlyCacheFrames = array(
		array('files' => NULL, 'items' => NULL)
	);

	/** @var array */
	private static $curlyBlocks = array();



	/**
	 * Curly cache helper.
	 * @return void
	 */
	public static function curlyAddFrame($tags)
	{
		array_unshift(self::$curlyCacheFrames, array(
			Cache::TAGS => $tags,
			Cache::FILES => array(end(self::$curlyCacheFrames[0][Cache::FILES])),
			Cache::ITEMS => NULL,
			Cache::EXPIRE => rand(86400 * 4, 86400 * 7),
		));
	}



	/**
	 * Callback for self::curlyBrackets.
	 */
	private static function curlyCb($m)
	{
		list(, $mod, $var, $modifiers) = $m;
		$var = trim($var);
		$var2 = NULL;

		if ($mod === 'block') {
			$tmp = $var === '' ? 'echo ' : $var . '=';
			$var = 'ob_get_clean()';

		} elseif ($mod === '/block' || $mod === '/cache') {
			$var = array_pop(self::$curlyBlocks);

		} elseif ($mod === 'cache') {
			$var2 = 'array(' . $var . ')';
			$var = var_export(uniqid(), TRUE); // TODO: odstranit uniqid
			$tmp = $var . ', ob_get_flush(), array_shift(TemplateFilters::$curlyCacheFrames)';
			$modifiers = NULL;

		} elseif ($mod === 'snippet') {
			if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) {
				$var = '"' . $m[1] . '"';
				if ($m[2]) $var .= ', ' . var_export($m[2], TRUE);
			}

		} elseif ($mod === '/snippet') {
			$var = '"' . $var . '"';

		} elseif ($mod === '$' || $mod === '!' || $mod === '!$') {
			$var = '$' . $var;

		} elseif ($mod === 'link' || $mod === 'plink' || $mod === 'ajaxlink' || $mod ===  'ifCurrent' || $mod ===  'include') {
			if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) {
				$var = strspn($m[1], '\'"$') ? $m[1] : "'$m[1]'";
				if ($m[2]) $var .= strncmp($m[2], 'array', 5) === 0 ? ", $m[2]" : ", array($m[2])";
				if ($mod === 'ifCurrent') $var = '$presenter->link(' . $var . '); ';
			}
			if ($mod === 'link') $var = '$control->link(' . $var .')';
			elseif ($mod === 'plink') $var = '$presenter->link(' . $var .')';
			elseif ($mod === 'ajaxlink') $var = '$control->ajaxlink(' . $var .')';
		}

		if ($modifiers) {
			foreach (explode('|', $modifiers) as $modifier) {
				$args = explode(':', $modifier);
				$modifier = $args[0];
				$args[0] = $var;
				$var = implode(', ', $args);
				$var = "\$template->$modifier($var)";
			}
		}

		if ($mod === 'block') {
			self::$curlyBlocks[] = $tmp . $var;

		} elseif ($mod === 'cache') {
			self::$curlyBlocks[] = $tmp;
		}

		return strtr(self::$curlyXlatMask[$mod], array('##' => $var2, '#' => $var));
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
	 *    <?nette filter="TemplateFilters::curlyBrackets"?>
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
			'#(src|href|action)\s*=\s*"(?![a-z]+:|/|<|\\#)#',
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