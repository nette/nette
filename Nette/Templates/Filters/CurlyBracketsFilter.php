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
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Template filter curlyBrackets: support for {...} in template.
 *
 * - {$variable} with escaping
 * - {!$variable} without escaping
 * - {*comment*} will be removed
 * - {=expression} echo with escaping
 * - {!=expression} echo without escaping
 * - {?expression} evaluate PHP statement
 * - {_expression} echo translation with escaping
 * - {!_expression} echo translation without escaping
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
 * - {assign $var value} set template parameter
 * - {debugbreak}
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
class CurlyBracketsFilter extends /*Nette\*/Object
{

	/** @var array */
	public static $macros = array(
		'block' => '<?php %:macroBlock% ?>',
		'/block' => '<?php %:macroBlockEnd% ?>',

		'snippet' => '<?php } if ($_cb->foo = $template->snippet($control%:macroSnippet%)) { $_cb->snippets[] = $_cb->foo; ?>',
		'/snippet' => '<?php array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) { ?>',

		'cache' => '<?php if ($_cb->foo = $template->cache($_cb->key = md5(__FILE__) . __LINE__, $template->getFile(), array(%%))) { $_cb->caches[] = $_cb->foo; ?>',
		'/cache' => '<?php array_pop($_cb->caches)->save(); } if (!empty($_cb->caches)) end($_cb->caches)->addItem($_cb->key); ?>',

		'if' => '<?php if (%%): ?>',
		'elseif' => '<?php elseif (%%): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'foreach' => '<?php foreach (%:macroForeach%): ?>',
		'/foreach' => '<?php endforeach; array_pop($_cb->its); $iterator = end($_cb->its) ?>',
		'for' => '<?php for (%%): ?>',
		'/for' => '<?php endfor ?>',
		'while' => '<?php while (%%): ?>',
		'/while' => '<?php endwhile ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',

		'ajaxlink' => '<?php echo $template->{$_cb->escape}(%:macroAjaxlink%) ?>',
		'plink' => '<?php echo $template->{$_cb->escape}(%:macroPlink%) ?>',
		'link' => '<?php echo $template->{$_cb->escape}(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent%; if ($presenter->getLastCreatedRequestFlag("current")): ?>',

		'attr' => '<?php echo Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php Environment::getHttpResponse()->setHeader("Content-Type", "%%") ?>',
		/*'contentType' => '<?php \Nette\Environment::getHttpResponse()->setHeader("Content-Type", "%%") ?>',*/
		'assign' => '<?php %:macroAssign% ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!_' => '<?php echo $template->translate(%:macroModifiers%) ?>',
		'!=' => '<?php echo %:macroModifiers% ?>',
		'_' => '<?php echo $template->{$_cb->escape}($template->translate(%:macroModifiers%)) ?>',
		'=' => '<?php echo $template->{$_cb->escape}(%:macroModifiers%) ?>',
		'!$' => '<?php echo %:macroVar% ?>',
		'!' => '<?php echo %:macroVar% ?>',
		'$' => '<?php echo $template->{$_cb->escape}(%:macroVar%) ?>',
		'?' => '<?php %:macroModifiers% ?>',
	);

	/** @var array */
	private $blocks = array();

	/** @var string */
	private $file;

	/** @var string */
	private $extends, $var, $modifiers;



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public static function invoke($s, $file)
	{
		$filter = new self;
		return $filter->__invoke($s, $file);
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s, $file)
	{
		$this->file = $file;
		$this->extends = NULL;

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support
		$s = "<?php\nif (SnippetHelper::\$outputAllowed) {\n?>$s<?php\n}\n?>";
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);

		// internal state holder
		$s = "<?php "
			/*. "use Nette\\Templates\\CurlyBracketsFilter, Nette\\SmartCachingIterator, Nette\\Environment, Nette\\Web\\Html, Nette\\Templates\\SnippetHelper;\n"*/
			. "\$_cb = CurlyBracketsFilter::initState(\$template) ?>" . $s;

		// add local content escaping switcher
		$s = preg_replace(array(
			'#(<script[^>]*>)(?!</)(.+)(</script)#Uis',
			'#(<style[^>]*>)(?!</)(.*)(</style)#Uis',
		), array(
			'$1<?php \\$_cb->escape = "escapeJs" ?>$2<?php \\$_cb->escape = "escape" ?>$3',
			'$1<?php \\$_cb->escape = "escapeCss" ?>$2<?php \\$_cb->escape = "escape" ?>$3',
		), $s);

		$this->blocks = array();
		$k = array();
		foreach (self::$macros as $key => $foo)
		{
			$key = preg_quote($key, '#');
			if (preg_match('#[a-zA-Z0-9]$#', $key)) {
				$key .= '(?=[^a-zA-Z0-9._-])';
			}
			$k[] = $key;
		}
		$s = preg_replace_callback(
			'#\\{(' . implode('|', $k) . ')([^}]*?)(\\|[a-z](?:[^\'"}\s|]+|\\|[a-z]|\'[^\']*\'|"[^"]*")*)?\\}()#s',
			array($this, 'cb'),
			$s
		);

		$s .= $this->extends;

		return $s;
	}



	/**
	 * Callback for replacing text.
	 */
	private function cb($m)
	{
		list(, $macro, $this->var, $this->modifiers) = $m;
		return preg_replace_callback('#%(.*?)%#', array($this, 'cb2'), self::$macros[$macro]);
	}



	/**
	 * Callback for replacing text.
	 */
	private function cb2($m)
	{
		if ($m[1]) {
			$callback = $m[1][0] === ':' ? array($this, substr($m[1], 1)) : $m[1];
			/**/fixCallback($callback);/**/
			if (!is_callable($callback)) {
				$able = is_callable($callback, TRUE, $textual);
				throw new /*\*/InvalidStateException("CurlyBrackets macro handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
			}
			return call_user_func($callback, trim($this->var), $this->modifiers);

		} else {
			return trim($this->var);
		}
	}



	/**
	 * {$var |modifiers}
	 */
	private function macroVar($var, $modifiers)
	{
		return $this->macroModifiers('$' . $var, $modifiers);
	}



	/**
	 * {include ...}
	 */
	private function macroInclude($var, $modifiers)
	{
		if (substr($var, 0, 1) === '#') {
			preg_match('#^.([^\s,]+),?\s*(.*)$#', $var, $m);
			$var = '$template->getParams()'; // get_defined_vars()
			if ($m[2]) {
				if (strncmp($m[2], 'array', 5) === 0) {
					trigger_error('CurlyBracketsFilter: do not use keyword \'array\' in {include ...} macro', E_USER_WARNING);
					$var = "$m[2] + $var";
				} else {
					$var = "array($m[2]) + $var";
				}
			}
			$var = 'call_user_func($_cb->cs[0], ' . $var. ')';
			if ($m[1] === 'parent') {
				return '$_cb->csX = array_shift($_cb->cs); ' . $var . '; array_unshift($_cb->cs, $_cb->csX)';

			} elseif ($m[1] === 'this') {
				return $var;

			} else {
				return '$_cb->cs = $_cb->f[' . var_export($m[1], TRUE) . ']; ' . $var;
			}
		}

		return 'echo ' . $this->macroModifiers('$template->subTemplate(' . $this->formatVars($var) . ')->__toString(TRUE)', $modifiers);
	}



	/**
	 * {extends ...}
	 */
	private function macroExtends($var)
	{
		$this->extends = '<?php ob_end_clean(); ' . $this->macroInclude($var, NULL) . '?>';
		return 'ob_start()';
	}



	/**
	 * {block ...}
	 */
	private function macroBlock($var, $modifiers)
	{
		if (substr($var, 0, 1) === '#') {
			$var = var_export(substr($var, 1), TRUE);
			$func = '_cbb' . substr(md5($this->file . "\00" . $var), 0, 15);
			$call = $this->extends ? '' : "\n\$_cb->cs = \$_cb->f[$var]; call_user_func(\$_cb->cs[0], \$template->getParams())"; // get_defined_vars()
			$this->blocks[] = "\n}\n\$_cb->f[$var][] = '$func';$call";
			return "\nfunction $func() { extract(func_get_arg(0))\n";
		}

		$this->blocks[] = '} catch (Exception $_e) { ob_end_clean(); throw $_e; } '
			. ($var === '' ? 'echo ' : $var . '=')
			. $this->macroModifiers('ob_get_clean()', $modifiers);
		return 'ob_start(); try {';
	}



	/**
	 * {/block ...}
	 */
	private function macroBlockEnd($var)
	{
		return array_pop($this->blocks);
	}



	/**
	 * {foreach ...}
	 */
	private function macroForeach($var)
	{
		return '$iterator = $_cb->its[] = new SmartCachingIterator(' . preg_replace('# +as +#i', ') as ', $var, 1);
	}



	/**
	 * {attr ...}
	 */
	private function macroAttr($var)
	{
		return str_replace(') ', ')->', $var . ' ');
	}



	/**
	 * {snippet ...}
	 */
	private function macroSnippet($var)
	{
		if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) {
			$var = ', "' . $m[1] . '"';
			if ($m[2]) $var .= ', ' . var_export($m[2], TRUE);
		}
		return $var;
	}



	/**
	 * {link ...}
	 */
	private function macroLink($var, $modifiers)
	{
		return $this->macroModifiers('$control->link(' . $this->formatVars($var) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	private function macroPlink($var, $modifiers)
	{
		return $this->macroModifiers('$presenter->link(' . $this->formatVars($var) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	private function macroIfCurrent($var, $modifiers)
	{
		return $var ? $this->macroModifiers('$presenter->link(' . $this->formatVars($var) .')', $modifiers) : '';
	}



	/**
	 * {ajaxlink ...}
	 */
	private function macroAjaxlink($var, $modifiers)
	{
		return $this->macroModifiers('$control->ajaxlink(' . $this->formatVars($var) .')', $modifiers);
	}



	/**
	 * {assign ...}
	 */
	private function macroAssign($var, $modifiers)
	{
		preg_match('#^\\$?(\S+)\s*(.*)$#', $var, $m);
		return '$template->' . $m[1] . ' = $' . $m[1] . ' = ' . $this->macroModifiers($m[2] === '' ? 'NULL' : $m[2], $modifiers);
	}



	/**
	 * Applies modifiers.
	 */
	public function macroModifiers($var, $modifiers)
	{
		if (!$modifiers) return $var;
		preg_match_all(
			'#[^\'"}\s|:]+|[|:]|\'[^\']*\'|"[^"]*"#s',
			$modifiers . '|',
			$tokens
		);
		$state = FALSE;
		foreach ($tokens[0] as $token) {
			if ($token === ':' || $token === '|') {
				if (!isset($prev)) {
					continue;

				} elseif ($state === FALSE) {
					$var = "\$template->$prev($var";

				} else {
					$var .= ', ' . $prev;
				}

				if ($token === '|') {
					$var .= ')';
					$state = FALSE;

				} else {
					$state = TRUE;
				}
			} else {
				$prev = $token;
			}
		}
		return $var;
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatVars($var)
	{
		if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) {
			$var = strspn($m[1], '\'"$') ? $m[1] : "'$m[1]'";
			if ($m[2]) {
				if (strncmp($m[2], 'array', 5) === 0) {
					trigger_error('CurlyBracketsFilter: do not use keyword \'array\' in {link ...} macro', E_USER_WARNING);
					$var .= ", $m[2]";
				} else {
					$var .= ', ' . (strpos($m[2], '=>') === FALSE ? $m[2] : "array($m[2])");
				}
			}
		}
		return $var;
	}



	/**
	 * Initializes state holder $_cb in template.
	 */
	public static function initState($template)
	{
		if (!isset($template->_cb)) {
			$template->_cb = (object) array('escape' => 'escape'); // escaping support
		}
		if (!empty($template->_cb->caches)) { // cache support
			end($template->_cb->caches)->addFile($template->getFile());
		}
		return $template->_cb;
	}

}