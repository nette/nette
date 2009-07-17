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
 * Template compile-time filter curlyBrackets supports for {...} in template.
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
	/** single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @var array */
	public static $defaultMacros = array(
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
		'continueIf' => '<?php if (%%) continue ?>',
		'breakIf' => '<?php if (%%) break ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',

		'plink' => '<?php echo %:macroEscape%(%:macroPlink%) ?>',
		'link' => '<?php echo %:macroEscape%(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent%; if ($presenter->getLastCreatedRequestFlag("current")): ?>',
		'widget' => '<?php %:macroWidget% ?>',

		'attr' => '<?php echo Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'assign' => '<?php %:macroAssign% ?>', // deprecated?
		'dump' => '<?php Debug::consoleDump(%:macroDump%, "Template " . str_replace(Environment::getVariable("templatesDir"), "\xE2\x80\xA6", $template->getFile())) ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!_' => '<?php echo $template->translate(%:formatModifiers%) ?>',
		'!=' => '<?php echo %:formatModifiers% ?>',
		'_' => '<?php echo %:macroEscape%($template->translate(%:formatModifiers%)) ?>',
		'=' => '<?php echo %:macroEscape%(%:formatModifiers%) ?>',
		'!$' => '<?php echo %:macroVar% ?>',
		'!' => '<?php echo %:macroVar% ?>', // deprecated
		'$' => '<?php echo %:macroEscape%(%:macroVar%) ?>',
		'?' => '<?php %:formatModifiers% ?>', // deprecated?
	);

	/** @var array */
	public $macros;

	/** @var array */
	private $blocks = array();

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;

	/** @var string */
	private $context, $escape, $tag;

	/**#@+ Context-aware escaping states */
	const CONTEXT_TEXT = 1;
	const CONTEXT_CDATA = 2;
	const CONTEXT_TAG = 3;
	const CONTEXT_ATTRIBUTE_SINGLE = "'";
	const CONTEXT_ATTRIBUTE_DOUBLE = '"';
	const CONTEXT_NONE = 4;
	const CONTEXT_COMMENT = 5;
	/**#@-*/



	/**
	 * Invokes filter.
	 * @deprecated
	 */
	public static function invoke($s)
	{
		$filter = new self;
		return $filter->__invoke($s);
	}



	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->macros = self::$defaultMacros;
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
		$this->extends = NULL;

		// context-aware escaping
		$this->context = self::CONTEXT_TEXT;
		$this->escape = 'TemplateHelpers::escapeHtml';
		$this->tag = NULL;

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support (temporary solution)
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);

		// process all {tags} and <tags/>
		$s = preg_replace_callback(
			'~
				<(/?)([a-z]+|!--)|                      ## 1,2) start tag: <tag </tag <!-- ; ignores <!DOCTYPE
				((?:--\\s*)?>)|                         ## 3) end tag
				(?<=\\s)(style|on[a-z]+)\s*=\s*(["\'])| ## 4,5) attribute
				(["\'])|                                ## 6) attribute delimiter
				(\n[ \t]*)?\\{([^\\s\'"{}]              ## 7,8) indent & macro begin
					(?>'.self::RE_STRING.'|[^\'"}]+)    ##   + single or double quoted string, chars
					*)\\}([\ \t]*(?=\r|\n))?            ##   + 9) newline
			~xsi',
			array($this, 'cbContent'),
			"\n" . $s
		);

		// blocks closing check
		if (count($this->blocks) === 1) { // auto-close last block
			$s .= $this->macro('/block', '', '');

		} elseif ($this->blocks) {
			throw new /*\*/InvalidStateException("There are some unclosed blocks.");
		}

		// snippets support (temporary solution)
		$s = "<?php\nif (SnippetHelper::\$outputAllowed) {\n?>$s<?php\n}\n?>";

		// extends support
		if ($this->namedBlocks || $this->extends) {
			$s = "<?php\n"
				. 'if ($_cb->extends) { ob_start(); }' . "\n"
				. '?>' . $s . "<?php\n"
				. 'if ($_cb->extends) { ob_end_clean(); $template->subTemplate($_cb->extends, get_defined_vars())->render(); }' . "\n";
		}

		// named blocks
		if ($this->namedBlocks) {
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$name = preg_quote($name, '#');
				$s = preg_replace_callback("#{block($name)} \?>(.*)<\?php {/block$name}#sU", array($this, 'cbNamedBlocks'), $s);
			}
			$s = "<?php\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n//\n// end of blocks\n//\n?>" . $s;
		}

		// internal state holder
		$s = "<?php\n"
			/*. 'use Nette\Templates\CurlyBracketsFilter, Nette\Templates\TemplateHelpers, Nette\SmartCachingIterator, Nette\Web\Html, Nette\Templates\SnippetHelper, Nette\Debug, Nette\Environment, Nette\Templates\CachingHelper;' . "\n\n"*/
			. "\$_cb = CurlyBracketsFilter::initRuntime(\$template, " . var_export($this->extends, TRUE) . "); unset(\$_extends);\n"
			. '?>' . $s;

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
		//    [8] => {macro...}
		//    [9] => newline?

		if (!empty($matches[8])) { // {macro|var|modifiers}
			list(, , , , , , , $indent, $macro) = $matches;

			if (preg_match('#^(.*?)(\\|[a-z](?:'.self::RE_STRING.'|[^\'"\s]+)*)$#i', $macro, $m)) {
				list(, $macro, $modifiers) = $m;
			}

			foreach ($this->macros as $key => $val) {
				if (strncmp($macro, $key, strlen($key)) === 0) {
					$var = substr($macro, strlen($key));
					if (preg_match('#[a-zA-Z0-9]$#', $key) && preg_match('#^[a-zA-Z0-9._-]#', $var)) {
						continue;
					}
					$result = $this->macro($key, trim($var), isset($modifiers) ? $modifiers : '');
					$nl = isset($matches[9]) ? "\n" : ''; // double newline
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
			} elseif ($this->context === self::CONTEXT_COMMENT && $matches[3] !== '>') { // --\s*>
				$this->context = self::CONTEXT_TEXT;
				$this->escape = 'TemplateHelpers::escapeHtml';
			}

		} elseif (empty($matches[1])) { // <tag
			if ($this->context === self::CONTEXT_TEXT) {
				if ($matches[2] === '!--') {
					$this->context = self::CONTEXT_COMMENT;
					$this->escape = 'TemplateHelpers::escapeHtmlComment';
				} else {
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
					$this->tag = strtolower($matches[2]);
				}
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
		return preg_replace_callback('#%(.*?)%#', array($this, 'cbMacro'), $this->macros[$macro]);
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
		return $this->formatModifiers('$' . $var, $modifiers);
	}



	/**
	 * {include ...}
	 */
	private function macroInclude($var, $modifiers)
	{
		$destination = $this->fetchToken($var); // destination [,] [params]
		$params = $this->formatArray($var) . ($var ? ' + ' : '');

		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {include}.");

		} elseif ($destination[0] === '#') { // include #block
			if (!preg_match('#^\\#'.self::RE_IDENTIFIER.'$#', $destination)) {
				throw new /*\*/InvalidStateException("Included block name must be alphanumeric string, '$destination' given.");
			}

			$parent = $destination === '#parent';
			if ($destination === '#parent' || $destination === '#this') {
				$item = end($this->blocks);
				while ($item && $item[0][0] !== '#') $item = prev($this->blocks);
				if (!$item) {
					throw new /*\*/InvalidStateException("Cannot include $name block outside of any block.");
				}
				$destination = $item[0];
			}
			$name = var_export($destination, TRUE);
			$params .= 'get_defined_vars()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_cb->blocks[$name]), $params)"
				: "CurlyBracketsFilter::callBlock" . ($parent ? 'Parent' : '') . "(\$_cb->blocks, $name, $params)";
			return $modifiers
				? "ob_start(); $cmd; echo " . $this->formatModifiers('ob_get_clean()', $modifiers)
				: $cmd;

		} else { // include "file"
			$destination = $this->formatString($destination);
			$params .= '$template->getParams()';
			return $modifiers
				? 'echo ' . $this->formatModifiers('$template->subTemplate(' . $destination . ', ' . $params . ')->__toString(TRUE)', $modifiers)
				: '$template->subTemplate(' . $destination . ', ' . $params . ')->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	private function macroExtends($var)
	{
		$destination = $this->fetchToken($var); // destination
		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {extends}.");
		}
		if (!empty($this->blocks)) {
			throw new /*\*/InvalidStateException("{extends} must be placed outside any block.");
		}
		if ($this->extends !== NULL) {
			throw new /*\*/InvalidStateException("Multiple {extends} declarations are not allowed.");
		}
		$this->extends = $destination !== 'none';
		return $this->extends ? '$_cb->extends = ' . $this->formatString($destination) : '';
	}



	/**
	 * {block ...}
	 */
	private function macroBlock($var, $modifiers)
	{
		$name = $this->fetchToken($var); // block [,] [params]

		if ($name === NULL || $name[0] === '$') { // anonymous block or capture
			$this->blocks[] = array($name, $modifiers);
			return ($name === NULL && $modifiers === '') ? '' : 'ob_start()';

		} elseif ($name[0] === '#') { // #block
			if (!preg_match('#^\\#'.self::RE_IDENTIFIER.'$#', $name)) {
				throw new /*\*/InvalidStateException("Block name must be alphanumeric string, '$name' given.");

			} elseif (isset($this->namedBlocks[$name])) {
				throw new /*\*/InvalidStateException("Cannot redeclare block '$name'.");
			}

			$top = empty($this->blocks);
			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array($name, '');
			if (!$top) {
				return $this->macroInclude($name, $modifiers) . "{block$name}";

			} elseif ($this->extends) {
				return "{block$name}";

			} else {
				return 'if (!$_cb->extends) { ' . $this->macroInclude($name, $modifiers) . "; } {block$name}";
			}

		} else {
			throw new /*\*/InvalidStateException("Invalid block parameter '$name'.");
		}
	}



	/**
	 * {/block ...}
	 */
	private function macroBlockEnd($var)
	{
		$empty = empty($this->blocks);
		list($name, $modifiers) = array_pop($this->blocks);

		if ($empty || ($var && $var !== $name)) {
			throw new /*\*/InvalidStateException("Tag {/block $var} was not expected here.");

		} elseif (substr($name, 0, 1) === '#') { // #block
			return "{/block$name}";

		} else { // anonymous block or capture
			return ($name === NULL && $modifiers === '') ? ''
				: ($name === NULL ? 'echo ' : $name . '=') . $this->formatModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * Converts {block#named}...{/block} to functions.
	 */
	private function cbNamedBlocks($matches)
	{
		list(, $name, $content) = $matches;
		$func = '_cbb' . substr(md5(uniqid($name)), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
		$this->namedBlocks[$name] = "//\n// block $name\n//\n"
			. "if (!function_exists(\$_cb->blocks[" . var_export($name, TRUE) . "][] = '$func')) { function $func() { extract(func_get_arg(0))\n?>$content<?php\n}}";
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
		$args = array('');
		if ($snippet = $this->fetchToken($var)) {  // [name [,]] [tag]
			$args[] = $this->formatString($snippet);
		}
		if ($var) {
			$args[] = $this->formatString($var);
		}
		return implode(', ', $args);
	}



	/**
	 * {widget ...}
	 */
	private function macroWidget($var, $modifiers)
	{
		// TODO: add support for $modifiers
		// TODO: check arguments
		$pair = explode(':', $this->fetchToken($var), 2);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = preg_match('#^'.self::RE_IDENTIFIER.'|$#', $method) ? "render$method" : "{\"render$method\"}";
		return "\$control->getWidget(\"$pair[0]\")->$method({$this->formatArray($var)})";
	}



	/**
	 * {link ...}
	 */
	private function macroLink($var, $modifiers)
	{
		return $this->formatModifiers('$control->link(' . $this->formatLink($var) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	private function macroPlink($var, $modifiers)
	{
		return $this->formatModifiers('$presenter->link(' . $this->formatLink($var) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	private function macroIfCurrent($var, $modifiers)
	{
		return $var ? $this->formatModifiers('$presenter->link(' . $this->formatLink($var) .')', $modifiers) : '';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink($var)
	{
		return $this->formatString($this->fetchToken($var)) . $this->formatArray($var, ', '); // destination [,] args
	}



	/**
	 * {assign ...}
	 */
	private function macroAssign($var, $modifiers)
	{
		$param = ltrim($this->fetchToken($var), '$'); // [$]params value
		return '$' . $param . ' = ' . $this->formatModifiers($var === '' ? 'NULL' : $var, $modifiers);
	}



	/**
	 * Escaping helper.
	 */
	private function macroEscape($var)
	{
		return $this->escape;
	}



	/********************* compile-time helpers ****************d*g**/



	/**
	 * Applies modifiers.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function formatModifiers($var, $modifiers)
	{
		if (!$modifiers) return $var;
		preg_match_all(
			'~
				'.self::RE_STRING.'|  ## single or double quoted string
				[^\'"|:,]+|           ## symbol
				[|:,]                 ## separator
			~xs',
			$modifiers . '|',
			$tokens
		);
		$inside = FALSE;
		$prev = '';
		foreach ($tokens[0] as $token) {
			if ($token === '|' || $token === ':' || $token === ',') {
				if ($prev === '') {

				} elseif (!$inside) {
					if (!preg_match('#^'.self::RE_IDENTIFIER.'$#', $prev)) {
						throw new /*\*/InvalidStateException("Modifier name must be alphanumeric string, '$prev' given.");
					}
					$var = "\$template->$prev($var";
					$prev = '';
					$inside = TRUE;

				} else {
					$var .= ', ' . self::formatString($prev);
					$prev = '';
				}

				if ($token === '|' && $inside) {
					$var .= ')';
					$inside = FALSE;
				}
			} else {
				$prev .= $token;
			}
		}
		return $var;
	}



	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public static function fetchToken(& $s)
	{
		if (preg_match('#^((?>'.self::RE_STRING.'|[^\'"\s,]+)+)\s*,?\s*(.*)$#', $s, $matches)) { // token [,] tail
			$s = $matches[2];
			return $matches[1];
		}
		return NULL;
	}



	/**
	 * Formats parameters to PHP array.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function formatArray($s, $prefix = '')
	{
		$s = preg_replace_callback(
			'~
				'.self::RE_STRING.'|                          ## single or double quoted string
				(?<=[,=(]|=>|^)\s*([a-z\d_]+)(?=\s*[,=)]|$)   ## 1) symbol
			~xi',
			array(__CLASS__, 'cbArgs'),
			trim($s)
		);
		return $s === '' ? '' : $prefix . "array($s)";
	}



	/**
	 * Callback for formatArgs().
	 */
	private static function cbArgs($matches)
	{
		//    [1] => symbol

		if (!empty($matches[1])) { // symbol
			list(, $symbol) = $matches;
			static $keywords = array('true'=>1, 'false'=>1, 'null'=>1, 'and'=>1, 'or'=>1, 'xor'=>1, 'clone'=>1, 'new'=>1);
			return is_numeric($symbol) || isset($keywords[strtolower($symbol)]) ? $matches[0] : "'$symbol'";

		} else {
			return $matches[0];
		}
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public static function formatString($s)
	{
		return (is_numeric($s) || strspn($s, '\'"$')) ? $s : '"' . $s . '"';
	}



	/********************* run-time helpers ****************d*g**/



	/**
	 * Calls block.
	 * @param  array
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlock(& $blocks, $name, $params)
	{
		if (empty($blocks[$name])) {
			throw new /*\*/InvalidStateException("Call to undefined block '$name'.");
		}
		$block = reset($blocks[$name]);
		$block($params);
	}



	/**
	 * Calls parent block.
	 * @param  array
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlockParent(& $blocks, $name, $params)
	{
		if (empty($blocks[$name]) || ($block = next($blocks[$name])) === FALSE) {
			throw new /*\*/InvalidStateException("Call to undefined parent block '$name'.");
		}
		$block($params);
	}



	/**
	 * Initializes state holder $_cb in template.
	 * @param  ITemplate
	 * @param  bool
	 * @return stdClass
	 */
	public static function initRuntime($template, $extends)
	{
		$cb = isset($template->_cb) ? $template->_cb : (object) NULL;
		unset($template->_cb);

		$cb->extends = is_bool($extends) ? $extends : (empty($template->_extends) ? FALSE : $template->_extends);
		unset($template->_extends);

		if (!empty($cb->caches)) { // cache support
			end($cb->caches)->addFile($template->getFile());
		}

		return $cb;
	}

}