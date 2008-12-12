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
 * Template filter curlyBrackets: support for {...} in template.
 *
 * - {$variable} with escaping
 * - {!$variable} without escaping
 * - {*comment*} will be removed
 * - {=expression} echo with escaping
 * - {!=expression} echo without escaping
 * - {?expression} evaluate PHP statement
 * - {_expression} echo with escaping and translation
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {ajaxlink destination ...} ajax link
 * - {if ?} ... {elseif ?} ... {else} ... {/if} // or <%else%>, <%/if%>, <%/foreach%> ?
 * - {for ?} ... {/for}
 * - {foreach ?} ... {/foreach}
 * - {include ?}
 * - {cache ?} ... {/cache} cached block
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {attr ?} HTML element attributes
 * - {block|texy} ... {/block} capture of filter block
 * - {contentType ...} HTTP Content-Type header
 * - {debugbreak}
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette\Templates
 */
final class CurlyBracketsFilter
{

	/** @var array */
	public static $statements = array(
		'block' => '<?php ob_start(); try { ?>',
		'/block' => '<?php } catch (Exception $_e) { ob_end_clean(); throw $_e; } # ?>',

		'snippet' => '<?php } if ($_cb->foo = $template->snippet($control#)) { $_cb->snippets[] = $_cb->foo; ?>',
		'/snippet' => '<?php array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) { ?>',

		'cache' => '<?php if ($_cb->foo = $template->cache($_cb->key = md5(__FILE__) . __LINE__, $template->getFile(), array(#))) { $_cb->caches[] = $_cb->foo; ?>',
		'/cache' => '<?php array_pop($_cb->caches)->save(); } if (!empty($_cb->caches)) end($_cb->caches)->addItem($_cb->key); ?>',

		'if' => '<?php if (#): ?>',
		'elseif' => '<?php elseif (#): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'foreach' => '<?php foreach (#): ?>',
		'/foreach' => '<?php endforeach; $iterator = array_pop($_cb->iterators); ?>',
		'for' => '<?php for (#): ?>',
		'/for' => '<?php endfor ?>',
		'while' => '<?php while (#): ?>',
		'/while' => '<?php endwhile ?>',

		'include' => '<?php $template->subTemplate(#)->render() ?>',
		'ajaxlink' => '<?php echo $template->{$_cb->escape}(#) ?>',
		'plink' => '<?php echo $template->{$_cb->escape}(#) ?>',
		'link' => '<?php echo $template->{$_cb->escape}(#) ?>',
		'ifCurrent' => '<?php #if ($presenter->getCreatedRequest() && $presenter->getCreatedRequest()->hasFlag("current")): ?>',

		'attr' => '<?php echo Html::el(NULL)->#attributes() ?>',
		'contentType' => '<?php Environment::getHttpResponse()->setHeader("Content-Type", "#") ?>',
		/*'contentType' => '<?php \Nette\Environment::getHttpResponse()->setHeader("Content-Type", "#") ?>',*/
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!=' => '<?php echo # ?>',
		'_' => '<?php echo $template->{$_cb->escape}($template->translate(#)) ?>',
		'=' => '<?php echo $template->{$_cb->escape}(#) ?>',
		'!$' => '<?php echo # ?>',
		'!' => '<?php echo # ?>',
		'$' => '<?php echo $template->{$_cb->escape}(#) ?>',
		'?' => '<?php # ?>',
	);



	/** @var array */
	private static $blocks = array();



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*\*/LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Invokes filter.
	 * @param  Template
	 * @param  string
	 * @return string
	 */
	public static function invoke($template, $s)
	{
		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support
		$s = "<?php\nif (SnippetHelper::\$outputAllowed) {\n?>$s<?php\n}\n?>";
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);

		// internal variable
		$s = "<?php\n"
			/*. "use Exception, Nette\\SmartCachingIterator, Nette\\Environment, Nette\\Web\\Html, Nette\\Templates\\SnippetHelper;\n"*/
			. "if (!isset(\$_cb)) \$_cb = \$template->_cb = (object) NULL;\n"  // internal variable
			. "if (empty(\$_cb->escape)) \$_cb->escape = 'escape';\n"  // content sensitive escaping
			. "if (!empty(\$_cb->caches)) end(\$_cb->caches)->addFile(\$template->getFile());\n" // cache support
			. "?>" . $s;

		// add local content escaping switcher
		$s = preg_replace(array(
			'#<script[^>]*>(?!</script>)#i',
			'#<style[^>]*>#i',
			'#(?<![\'"]>)</script#i',
			'#</style#i',
		), array(
			'$0<?php \\$_cb->escape = "escapeJs" ?>',
			'$0<?php \\$_cb->escape = "escapeCss" ?>',
			'<?php \\$_cb->escape = "escape" ?>$0',
			'<?php \\$_cb->escape = "escape" ?>$0',
		), $s);

		self::$blocks = array();
		$k = array();
		foreach (self::$statements as $key => $foo)
		{
			$key = preg_quote($key, '#');
			if (preg_match('#[a-zA-Z0-9]$#', $key)) {
				$key .= '(?=[|}\s])';
			}
			$k[] = $key;
		}
		$s = preg_replace_callback(
			'#\\{(' . implode('|', $k) . ')([^}]*?)(?:\\|([a-z](?:[^\'"}\s|]+|\\|[a-z]|\'[^\']*\'|"[^"]*")*))?\\}()#s',
			array(__CLASS__, 'cb'),
			$s
		);

		return $s;
	}



	/**
	 * Callback.
	 */
	private static function cb($m)
	{
		list(, $mod, $var, $modifiers) = $m;
		$var = trim($var);
		$var2 = NULL;

		if ($mod === 'block') {
			$tmp = $var === '' ? 'echo ' : $var . '=';
			$var = 'ob_get_clean()';

		} elseif ($mod === '/block') {
			$var = array_pop(self::$blocks);

		} elseif ($mod === 'foreach') {
			$var = '$iterator = $_cb->iterators[] = new SmartCachingIterator(' . preg_replace('# +as +#i', ') as ', $var, 1);

		} elseif ($mod === 'attr') {
			$var = str_replace(') ', ')->', $var . ' ');

		} elseif ($mod === 'snippet') {
			if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) {
				$var = ', "' . $m[1] . '"';
				if ($m[2]) $var .= ', ' . var_export($m[2], TRUE);
			}

		} elseif ($mod === '/snippet') {
			$var = ', "' . $var . '"';

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
			self::$blocks[] = $tmp . $var;
		}

		return strtr(self::$statements[$mod], array('#' => $var));
	}

}