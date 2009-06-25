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
 * - {dump $var}
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

		'snippet' => '<?php } if ($_cb->foo = SnippetHelper::create($control%:macroSnippet%)) { $_cb->snippets[] = $_cb->foo; ?>',
		'/snippet' => '<?php array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) { ?>',

		'cache' => '<?php if ($_cb->foo = CachingHelper::create($_cb->key = md5(__FILE__) . __LINE__, $template->getFile(), array(%%))) { $_cb->caches[] = $_cb->foo; ?>',
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
		'continue' => '<?php continue ?>',
		'break' => '<?php break ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',

		'ajaxlink' => '<?php echo %:macroEscape%(%:macroAjaxlink%) ?>',
		'plink' => '<?php echo %:macroEscape%(%:macroPlink%) ?>',
		'link' => '<?php echo %:macroEscape%(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent%; if ($presenter->getLastCreatedRequestFlag("current")): ?>',

		'attr' => '<?php echo Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'assign' => '<?php %:macroAssign% ?>',
		'dump' => '<?php Debug::consoleDump(%:macroDump%, "Template " . str_replace(Environment::getVariable("templatesDir"), "\xE2\x80\xA6", $template->getFile())) ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!_' => '<?php echo $template->translate(%:macroModifiers%) ?>',
		'!=' => '<?php echo %:macroModifiers% ?>',
		'_' => '<?php echo %:macroEscape%($template->translate(%:macroModifiers%)) ?>',
		'=' => '<?php echo %:macroEscape%(%:macroModifiers%) ?>',
		'!$' => '<?php echo %:macroVar% ?>',
		'!' => '<?php echo %:macroVar% ?>',
		'$' => '<?php echo %:macroEscape%(%:macroVar%) ?>',
		'?' => '<?php %:macroModifiers% ?>',
	);

	/** @var array */
	private $blocks = array();

	/** @var array */
	private $namedBlocks = array();

	/** @var string */
	private $context, $escape, $tag;

	/**#@+ Context-aware escaping states */
	const CONTEXT_TEXT = 1;
	const CONTEXT_CDATA = 2;
	const CONTEXT_TAG = 3;
	const CONTEXT_ATTRIBUTE_SINGLE = "'";
	const CONTEXT_ATTRIBUTE_DOUBLE = '"';
	const CONTEXT_NONE = 4;
	/**#@-*/



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public static function invoke($s)
	{
		$filter = new self;
		return $filter->__invoke($s);
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		$this->blocks = array();
		$this->namedBlocks = array();

		// context-aware escaping
		$this->context = self::CONTEXT_TEXT;
		$this->escape = 'TemplateHelpers::escapeHtml';
		$this->tag = NULL;

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support (temporary solution)
		$s = "<?php\nif (SnippetHelper::\$outputAllowed) {\n?>\n$s<?php\n}\n?>"; // \n$s is required by following RE
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);

		// process all {tags} and <tags/>
		$s = preg_replace_callback('~
				<(/?)([a-z]+)|                          ## 1,2) start tag: <tag </tag ; ignores <!-- <!DOCTYPE
				(>)|                                    ## 3) end tag
				(?<=\\s)(style|on[a-z]+)\s*=\s*(["\'])| ## 4,5) attribute
				(["\'])|                                ## 6) attribute delimiter
				(\n[ \t]*)?\\{([^\\s\'"{}][^}]*?)(\\|[a-z](?:[^\'"}\s|]+|\\|[a-z]|\'[^\']*\'|"[^"]*")*)?\\}([ \t]*(?=\r|\n))? ## 7,8,9,10) indent & macro & modifiers & newline
			~xsi',
			array($this, 'cbContent'),
			$s
		);

		// named blocks
		if ($this->namedBlocks) {
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$s = preg_replace_callback("#{block\#($name)} \?>(.*)<\?php {/block\#$name}#sU", array($this, 'cbNamedBlocks'), $s);
			}
			preg_match('#function (\S+)\(#', reset($this->namedBlocks), $m);
			$s = "<?php\nif (!function_exists('$m[1]')) {\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n} ?>" . $s;
		}

		// internal state holder
		$s = "<?php "
			/*. "use Nette\\Templates\\CurlyBracketsFilter, Nette\\Templates\\TemplateHelpers, Nette\\SmartCachingIterator, Nette\\Web\\Html, Nette\\Templates\\SnippetHelper, Nette\\Debug, Nette\\Environment, Nette\\Templates\\CachingHelper;\n"*/
			. "\$_cb = CurlyBracketsFilter::initState(\$template) ?>" . $s;

		return $s;
	}



	/**
	 * Searches for curly brackets, HTML tags and attributes.
	 */
	private function cbContent($matches)
	{
		//    [1] => /
		//    [2] => tag
		//    [3] => >
		//    [4] => style|on...
		//    [5] => '"
		//    [6] => '"
		//    [7] => indent
		//    [8] => {macro
		//    [9] => {...|modifiers}
		//    [10] => newline?

		if (!empty($matches[8])) { // {macro|var|modifiers}
			$matches[] = NULL;
			list(, , , , , , , $indent, $macro, $modifiers) = $matches;
			foreach (self::$macros as $key => $val) {
				if (strncmp($macro, $key, strlen($key)) === 0) {
					$var = substr($macro, strlen($key));
					if (preg_match('#[a-zA-Z0-9]$#', $key) && preg_match('#^[a-zA-Z0-9._-]#', $var)) {
						continue;
					}
					$result = $this->macro($key, trim($var), $modifiers);
					$nl = isset($matches[10]) ? "\n" : ''; // double newline
					if ($nl && $indent && strncmp($result, '<?php echo ', 11)) {
						return "\n" . $result; // remove indent, single newline
					} else {
						return $indent . $result . $nl;
					}
				}
			}
			throw new /*\*/InvalidStateException("Unknown macro '$matches[0]'.");

		} elseif ($this->context === self::CONTEXT_NONE) {
			// skip analyse

		} elseif (!empty($matches[6])) { // (attribute) '"
			if ($this->context === $matches[6]) {
				$this->context = self::CONTEXT_TAG;
				$this->escape = 'TemplateHelpers::escapeHtml';
			} elseif ($this->context === self::CONTEXT_TAG) {
				$this->context = $matches[6];
			}

		} elseif (!empty($matches[4])) { // (style|on...) '"
			if ($this->context === self::CONTEXT_TAG) {
				$this->context = $matches[5]; // self::CONTEXT_ATTRIBUTE_SINGLE || self::CONTEXT_ATTRIBUTE_DOUBLE
				$this->escape = strncasecmp($matches[4], 'on', 2) ? 'TemplateHelpers::escapeHtmlCss' : 'TemplateHelpers::escapeHtmlJs';
			}

		} elseif (!empty($matches[3])) { // >
			if ($this->context === self::CONTEXT_TAG) {
				if ($this->tag === 'script' || $this->tag === 'style') {
					$this->context = self::CONTEXT_CDATA;
					$this->escape = $this->tag === 'script' ? 'TemplateHelpers::escapeJs' : 'TemplateHelpers::escapeCss';
				} else {
					$this->context = self::CONTEXT_TEXT;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
			}

		} elseif (empty($matches[1])) { // <tag
			if ($this->context === self::CONTEXT_TEXT) {
				$this->context = self::CONTEXT_TAG;
				$this->escape = 'TemplateHelpers::escapeHtml';
				$this->tag = strtolower($matches[2]);
			}

		} else { // </tag
			if ($this->context === self::CONTEXT_TEXT || ($this->context === self::CONTEXT_CDATA && $this->tag === strtolower($matches[2]))) {
				$this->context = self::CONTEXT_TAG;
				$this->escape = 'TemplateHelpers::escapeHtml';
				$this->tag = NULL;
			}
		}
		return $matches[0];
	}



	/**
	 * Process specified macro.
	 */
	public function macro($macro, $var, $modifiers)
	{
		$this->_cbMacro = array($var, $modifiers);
		return preg_replace_callback('#%(.*?)%#', array($this, 'cbMacro'), self::$macros[$macro]);
	}



	/** @var array */
	private $_cbMacro;

	/**
	 * Callback for self::macro().
	 */
	private function cbMacro($m)
	{
		list($var, $modifiers) = $this->_cbMacro;
		if ($m[1]) {
			$callback = $m[1][0] === ':' ? array($this, substr($m[1], 1)) : $m[1];
			/**/fixCallback($callback);/**/
			if (!is_callable($callback)) {
				$able = is_callable($callback, TRUE, $textual);
				throw new /*\*/InvalidStateException("CurlyBrackets macro handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
			}
			return call_user_func($callback, $var, $modifiers);

		} else {
			return $var;
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
			preg_match('#^.([^\s,]+),?\s*(.*)$()#', $var, $m); // #name[,] [params]
			list(, $name, $params) = $m;

			if (!preg_match('#^[a-zA-Z0-9_]+$#', $name)) {
				throw new /*\*/InvalidStateException("Included block name must be alphanumeric string, '$name' given.");
			}

			$params = ($params ? "array($params) + " : '') . '$template->getParams()'; // or get_defined_vars() ?
			$cmd = $name === 'parent' ? 'next' : 'reset';
			if ($name === 'parent' || $name === 'this') {
				$item = end($this->blocks);
				while ($item && $item[0][0] !== '#') $item = prev($this->blocks);
				if (!$item) {
					throw new /*\*/InvalidStateException("Cannot include $name block outside of any block.");
				}
				$name = substr($item[0], 1);
			}
			$name = var_export($name, TRUE);
			$cmd = "call_user_func($cmd(\$_cb->blks[$name]), $params)";
			return $modifiers ? $this->macroBlock('', $modifiers) . $cmd . ";" . $this->macroBlockEnd(NULL) : $cmd;
		}

		return 'echo ' . $this->macroModifiers('$template->subTemplate(' . $this->formatVars($var) . ')->__toString(TRUE)', $modifiers);
	}



	/**
	 * {extends ...}
	 */
	private function macroExtends($var)
	{
		return $this->macroInclude($var, '') . '; return';
	}



	/**
	 * {block ...}
	 */
	private function macroBlock($var, $modifiers)
	{
		if (substr($var, 0, 1) === '#') { // named block
			$name = substr($var, 1);
			if (!preg_match('#^[a-zA-Z0-9_]+$#', $name)) {
				throw new /*\*/InvalidStateException("Block name must be alphanumeric string, '$name' given.");

			} elseif (isset($this->namedBlocks[$name])) {
				throw new /*\*/InvalidStateException("Cannot redeclare block '$name'.");
			}

			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array($var, '');
			return $this->macroInclude($var, $modifiers) . "{block#$name}";
		}

		if ($var === '' || $var[0] === '$') { // capture or modifier
			$this->blocks[] = array($var, $modifiers);
			return ($var === '' && $modifiers === '') ? '' : 'ob_start(); try {';
		}

		throw new /*\*/InvalidStateException("Invalid block parameter '$var'.");
	}



	/**
	 * {/block ...}
	 */
	private function macroBlockEnd($optVar)
	{
		list($var, $modifiers) = array_pop($this->blocks);

		if ($optVar && $optVar !== $var) {
			throw new /*\*/InvalidStateException("Tag {/block $var} was not expected here.");

		} elseif (substr($var, 0, 1) === '#') { // named block
			return "{/block$var}";

		} else { // capture or modifier
			return ($var === '' && $modifiers === '') ? ''
				: '} catch (Exception $_e) { ob_end_clean(); throw $_e; } '
				. ($var === '' ? 'echo ' : $var . '=')
				. $this->macroModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * Converts {block#named}...{/block} to functions.
	 */
	private function cbNamedBlocks($matches)
	{
		list(, $name, $content) = $matches;
		$func = '_cbb' . substr(md5(uniqid($name)), 0, 15) . '_' . $name;
		$this->namedBlocks[$name] = "\$_cb->blks[" . var_export($name, TRUE) . "][] = '$func';\n"
			. "function $func() { extract(func_get_arg(0)) // block #$name\n?>$content<?php\n}";
		return '';
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
	 * {contentType ...}
	 */
	private function macroContentType($var)
	{
		if (strpos($var, 'html') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeHtml';
			$this->context = self::CONTEXT_TEXT;

		} elseif (strpos($var, 'xml') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeXml';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'javascript') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeJs';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'css') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeCss';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'plain') !== FALSE) {
			$this->escape = '';
			$this->context = self::CONTEXT_NONE;

		} else {
			$this->escape = '$template->escape';
			$this->context = self::CONTEXT_NONE;
		}

		// temporary solution
		return strpos($var, '/') ? /*\Nette\*/'Environment::getHttpResponse()->setHeader("Content-Type", "' . $var . '")' : '';
	}



	/**
	 * {dump ...}
	 */
	private function macroDump($var)
	{
		return $var ? "array('$var' => $var)" : 'get_defined_vars()';
	}



	/**
	 * {snippet ...}
	 */
	private function macroSnippet($var)
	{
		if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) { // [name[,]] [tag]
			$var = ', "' . $m[1] . '"' . ($m[2] ? ', ' . var_export($m[2], TRUE) : '');
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
		preg_match('#^\\$?(\S+)\s*(.*)$#', $var, $m); // [$]params value
		return '$template->' . $m[1] . ' = $' . $m[1] . ' = ' . $this->macroModifiers($m[2] === '' ? 'NULL' : $m[2], $modifiers);
	}



	/**
	 * Escaping helper.
	 */
	private function macroEscape($var)
	{
		return $this->escape;
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
		if (preg_match('#^([^\s,]+),?\s*(.*)$#', $var, $m)) { // destination[,] args
			$var = strspn($m[1], '\'"$') ? $m[1] : "'$m[1]'";
			if ($m[2]) {
				$var .= ', ' . $this->formatArray($m[2]);
			}
		}
		return $var;
	}



	/**
	 * Formats parameters to PHP array syntax.
	 * @param  string
	 * @return string
	 */
	public static function formatArray($s)
	{
		$s = preg_replace_callback(
			'/(?:
				"(?:\\\\"|[^"])*"|             ## double quoted string
				\'(?:\\\\\'|[^\'])*\'|         ## single quoted string
				(?<=[,=(]|=>|^)\s*([a-z\d_]+)(?=\s*[,=)]|$)|   ## 1) symbol
				(?<![=><!])(=)(?![=><!])       ## 2) equal sign
			)/xsi',
			array(__CLASS__, 'cbArgs'),
			trim($s)
		);
		return "array($s)";
	}



	/**
	 * Callback for formatArgs().
	 */
	private static function cbArgs($matches)
	{
		//    [1] => symbol
		//    [2] => equal sign

		if (!empty($matches[2])) { // equal sign
			return '=>';

		} elseif (!empty($matches[1])) { // symbol
			list(, $symbol) = $matches;
			static $keywords = array('true'=>1, 'false'=>1, 'null'=>1, 'and'=>1, 'or'=>1, 'xor'=>1, 'clone'=>1, 'new'=>1);
			return is_numeric($symbol) || isset($keywords[strtolower($symbol)]) ? $matches[0] : "'$symbol'";

		} else {
			return $matches[0];
		}
	}



	/**
	 * Initializes state holder $_cb in template.
	 * @param  ITemplate
	 * @return stdClass
	 */
	public static function initState($template)
	{
		if (!isset($template->_cb)) {
			$template->_cb = (object) NULL;
		}
		if (!empty($template->_cb->caches)) { // cache support
			end($template->_cb->caches)->addFile($template->getFile());
		}
		return $template->_cb;
	}

}