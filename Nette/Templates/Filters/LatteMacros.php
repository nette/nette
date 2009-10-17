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
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Default macros for filter LatteFilter.
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
 * - {if ?} ... {elseif ?} ... {else} ... {/if}
 * - {ifset ?} ... {elseifset ?} ... {/if}
 * - {for ?} ... {/for}
 * - {foreach ?} ... {/foreach}
 * - {include ?}
 * - {cache ?} ... {/cache} cached block
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {attr ?} HTML element attributes
 * - {block|texy} ... {/block} block
 * - {contentType ...} HTTP Content-Type header
 * - {status ...} HTTP status
 * - {capture ?} ... {/capture} capture block to parameter
 * - {assign var => value} set template parameter
 * - {default var => value} set default template parameter
 * - {dump $var}
 * - {debugbreak}
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
class LatteMacros extends /*Nette\*/Object
{
	/** @var array */
	public static $defaultMacros = array(
		'syntax' => '%:macroSyntax%',
		'/syntax' => '%:macroSyntax%',

		'block' => '<?php %:macroBlock% ?>',
		'/block' => '<?php %:macroBlockEnd% ?>',

		'capture' => '<?php %:macroCapture% ?>',
		'/capture' => '<?php %:macroCaptureEnd% ?>',

		'snippet' => '<?php %:macroSnippet% ?>',
		'/snippet' => '<?php %:macroSnippetEnd% ?>',

		'cache' => '<?php if ($_cb->foo = CachingHelper::create($_cb->key = md5(__FILE__) . __LINE__, $template->getFile(), array(%%))) { $_cb->caches[] = $_cb->foo ?>',
		'/cache' => '<?php array_pop($_cb->caches)->save(); } if (!empty($_cb->caches)) end($_cb->caches)->addItem($_cb->key) ?>',

		'if' => '<?php if (%%): ?>',
		'elseif' => '<?php elseif (%%): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'ifset' => '<?php if (isset(%%)): ?>',
		'/ifset' => '<?php endif ?>',
		'elseifset' => '<?php elseif (isset(%%)): ?>',
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
		'control' => '<?php %:macroWidget% ?>',

		'attr' => '<?php echo Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'status' => '<?php Environment::getHttpResponse()->setCode(%%) ?>',
		'assign' => '<?php %:macroAssign% ?>', // deprecated?
		'default' => '<?php %:macroDefault% ?>',
		'dump' => '<?php Debug::consoleDump(%:macroDump%, "Template " . str_replace(Environment::getVariable("appDir"), "\xE2\x80\xA6", $template->getFile())) ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!_' => '<?php echo %:macroTranslate% ?>',
		'!=' => '<?php echo %:macroModifiers% ?>',
		'_' => '<?php echo %:macroEscape%(%:macroTranslate%) ?>',
		'=' => '<?php echo %:macroEscape%(%:macroModifiers%) ?>',
		'!$' => '<?php echo %:macroVar% ?>',
		'!' => '<?php echo %:macroVar% ?>', // deprecated
		'$' => '<?php echo %:macroEscape%(%:macroVar%) ?>',
		'?' => '<?php %:macroModifiers% ?>', // deprecated?
	);

	/** @var array */
	public $macros;

	/** @var LatteFilter */
	private $filter;

	/** @var array */
	private $current;

	/** @var array */
	private $blocks = array();

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;

	/** @var string */
	private $uniq;

	/**#@+ @internal block type */
	const BLOCK_NAMED = 1;
	const BLOCK_CAPTURE = 2;
	const BLOCK_ANONYMOUS = 3;
	/**#@-*/



	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->macros = self::$defaultMacros;
	}



	/**
	 * Initializes parsing.
	 * @param  LatteFilter
	 * @param  string
	 * @return void
	 */
	public function initialize($filter, & $s)
	{
		$this->filter = $filter;
		$this->blocks = array();
		$this->namedBlocks = array();
		$this->extends = NULL;
		$this->uniq = substr(md5(uniqid()), 0, 10);

		$filter->context = LatteFilter::CONTEXT_TEXT;
		$filter->escape = 'TemplateHelpers::escapeHtml';

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support (temporary solution)
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);
	}



	/**
	 * Finishes parsing.
	 * @param  string
	 * @return void
	 */
	public function finalize(& $s)
	{
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
				. 'if ($_cb->extends) { ob_end_clean(); LatteMacros::includeTemplate($_cb->extends, get_defined_vars(), $template)->render(); }' . "\n";
		}

		// named blocks
		if ($this->namedBlocks) {
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$name = preg_quote($name, '#');
				$s = preg_replace_callback("#{block ($name)} \?>(.*)<\?php {/block $name}#sU", array($this, 'cbNamedBlocks'), $s);
			}
			$s = "<?php\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n//\n// end of blocks\n//\n?>" . $s;
		}

		// internal state holder
		$s = "<?php\n"
			/*. 'use Nette\Templates\LatteMacros, Nette\Templates\TemplateHelpers, Nette\SmartCachingIterator, Nette\Web\Html, Nette\Templates\SnippetHelper, Nette\Debug, Nette\Environment, Nette\Templates\CachingHelper, Nette\Application\InvalidLinkException;' . "\n\n"*/
			. "\$_cb = LatteMacros::initRuntime(\$template, " . var_export($this->extends, TRUE) . ", " . var_export($this->uniq, TRUE) . "); unset(\$_extends);\n"
			. '?>' . $s;
	}



	/**
	 * Process {macro content | modifiers}
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function macro($macro, $content, $modifiers)
	{
		if ($macro === '') {
			$macro = substr($content, 0, 2);
			if (!isset($this->macros[$macro])) {
				$macro = substr($content, 0, 1);
				if (!isset($this->macros[$macro])) {
					return NULL;
				}
			}
			$content = substr($content, strlen($macro));

		} elseif (!isset($this->macros[$macro])) {
			return NULL;
		}
		$this->current = array($content, $modifiers);
		return preg_replace_callback('#%(.*?)%#', array($this, 'cbMacro'), $this->macros[$macro]);
	}



	/**
	 * Callback for self::macro().
	 */
	private function cbMacro($m)
	{
		list($content, $modifiers) = $this->current;
		if ($m[1]) {
			$callback = $m[1][0] === ':' ? array($this, substr($m[1], 1)) : $m[1];
			/**/fixCallback($callback);/**/
			if (!is_callable($callback)) {
				$able = is_callable($callback, TRUE, $textual);
				throw new /*\*/InvalidStateException("Latte macro handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
			}
			return call_user_func($callback, $content, $modifiers);

		} else {
			return $content;
		}
	}



	/**
	 * Process <n:tag attr> (experimental).
	 * @param  string
	 * @param  array
	 * @param  bool
	 * @return string
	 */
	public function tagMacro($name, $attrs, $closing)
	{
		$knownTags = array(
			'include' => 'block',
			'for' => 'each',
			'block' => 'name',
			'if' => 'cond',
			'elseif' => 'cond',
		);
		return $this->macro(
			$closing ? "/$name" : $name,
			isset($knownTags[$name], $attrs[$knownTags[$name]]) ? $attrs[$knownTags[$name]] : substr(var_export($attrs, TRUE), 8, -1),
			isset($attrs['modifiers']) ? $attrs['modifiers'] : ''
		);
	}



	/**
	 * Process <tag n:attr> (experimental).
	 * @param  string
	 * @param  array
	 * @param  bool
	 * @return string
	 */
	public function attrsMacro($code, $attrs, $closing)
	{
		$left = $right = '';
		foreach ($this->macros as $name => $foo) {
			if (!isset($this->macros["/$name"])) { // must be pair-macro
				continue;
			}

			$macro = $closing ? "/$name" : $name;
			if (isset($attrs[$name])) {
				if ($closing) {
					$right .= $this->macro($macro, '', '');
				} else {
					$left = $this->macro($macro, $attrs[$name], '') . $left;
				}
			}

			$innerName = "inner-$name";
			if (isset($attrs[$innerName])) {
				if ($closing) {
					$left .= $this->macro($macro, '', '');
				} else {
					$right = $this->macro($macro, $attrs[$innerName], '') . $right;
				}
			}

			$tagName = "tag-$name";
			if (isset($attrs[$tagName])) {
				$left = $this->macro($name, $attrs[$tagName], '') . $left;
				$right .= $this->macro("/$name", '', '');
			}

			unset($attrs[$name], $attrs[$innerName], $attrs[$tagName]);
		}

		return $attrs ? NULL : $left . $code . $right;
	}



	/********************* macros ****************d*g**/



	/**
	 * {$var |modifiers}
	 */
	private function macroVar($var, $modifiers)
	{
		return LatteFilter::formatModifiers('$' . $var, $modifiers);
	}



	/**
	 * {_$var |modifiers}
	 */
	private function macroTranslate($var, $modifiers)
	{
		return LatteFilter::formatModifiers($var, 'translate|' . $modifiers);
	}



	/**
	 * {syntax ...}
	 */
	private function macroSyntax($var)
	{
		switch ($var) {
		case '':
		case 'latte':
			$this->filter->setDelimiters('\\{(?![\\s\'"{}])', '\\}'); // {...}
			break;

		case 'double':
			$this->filter->setDelimiters('\\{\\{(?![\\s\'"{}])', '\\}\\}'); // {{...}}
			break;

		case 'asp':
			$this->filter->setDelimiters('<%\s*', '\s*%>'); // <%...%>
			break;

		case 'python':
			$this->filter->setDelimiters('\\{[{%]\s*', '\s*[%}]\\}'); // {% ... %} | {{ ... }}
			break;

		case 'off':
			$this->filter->setDelimiters('[^\x00-\xFF]', '');
			break;

		default:
			throw new /*\*/InvalidStateException("Unknown macro syntax '$var' on line {$this->filter->line}.");
		}
	}



	/**
	 * {include ...}
	 */
	private function macroInclude($content, $modifiers)
	{
		$destination = LatteFilter::fetchToken($content); // destination [,] [params]
		$params = LatteFilter::formatArray($content) . ($content ? ' + ' : '');

		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {include} on line {$this->filter->line}.");

		} elseif ($destination[0] === '#') { // include #block
			$destination = ltrim($destination, '#');
			if (!preg_match('#^'.LatteFilter::RE_IDENTIFIER.'$#', $destination)) {
				throw new /*\*/InvalidStateException("Included block name must be alphanumeric string, '$destination' given on line {$this->filter->line}.");
			}

			$parent = $destination === 'parent';
			if ($destination === 'parent' || $destination === 'this') {
				$item = end($this->blocks);
				while ($item && $item[0] !== self::BLOCK_NAMED) $item = prev($this->blocks);
				if (!$item) {
					throw new /*\*/InvalidStateException("Cannot include $destination block outside of any block on line {$this->filter->line}.");
				}
				$destination = $item[1];
			}
			$name = var_export($destination, TRUE);
			$params .= 'get_defined_vars()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_cb->blocks[$name]), $params)"
				: "LatteMacros::callBlock" . ($parent ? 'Parent' : '') . "(\$_cb->blocks, $name, $params)";
			return $modifiers
				? "ob_start(); $cmd; echo " . LatteFilter::formatModifiers('ob_get_clean()', $modifiers)
				: $cmd;

		} else { // include "file"
			$destination = LatteFilter::formatString($destination);
			$params .= '$template->getParams()';
			return $modifiers
				? 'echo ' . LatteFilter::formatModifiers('LatteMacros::includeTemplate(' . $destination . ', ' . $params . ', $_cb->templates[' . var_export($this->uniq, TRUE) . '])->__toString(TRUE)', $modifiers)
				: 'LatteMacros::includeTemplate(' . $destination . ', ' . $params . ', $_cb->templates[' . var_export($this->uniq, TRUE) . '])->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	private function macroExtends($content)
	{
		$destination = LatteFilter::fetchToken($content); // destination
		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {extends} on line {$this->filter->line}.");
		}
		if (!empty($this->blocks)) {
			throw new /*\*/InvalidStateException("{extends} must be placed outside any block; on line {$this->filter->line}.");
		}
		if ($this->extends !== NULL) {
			throw new /*\*/InvalidStateException("Multiple {extends} declarations are not allowed; on line {$this->filter->line}.");
		}
		$this->extends = $destination !== 'none';
		return $this->extends ? '$_cb->extends = ' . LatteFilter::formatString($destination) : '';
	}



	/**
	 * {block ...}
	 */
	private function macroBlock($content, $modifiers)
	{
		if (substr($content, 0, 1) === '$') { // capture - back compatibility
			trigger_error("Capturing {block $content} is deprecated; use {capture $content} instead on line {$this->filter->line}.", E_USER_WARNING);
			return $this->macroCapture($content, $modifiers);
		}

		$name = LatteFilter::fetchToken($content); // block [,] [params]

		if ($name === NULL) { // anonymous block
			$this->blocks[] = array(self::BLOCK_ANONYMOUS, NULL, $modifiers);
			return $modifiers === '' ? '' : 'ob_start()';

		} else { // #block
			$name = ltrim($name, '#');
			if (!preg_match('#^'.LatteFilter::RE_IDENTIFIER.'$#', $name)) {
				throw new /*\*/InvalidStateException("Block name must be alphanumeric string, '$name' given on line {$this->filter->line}.");

			} elseif (isset($this->namedBlocks[$name])) {
				throw new /*\*/InvalidStateException("Cannot redeclare block '$name'; on line {$this->filter->line}.");
			}

			$top = empty($this->blocks);
			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array(self::BLOCK_NAMED, $name, '');
			if (!$top) {
				return $this->macroInclude('#' . $name, $modifiers) . "{block $name}";

			} elseif ($this->extends) {
				return "{block $name}";

			} else {
				return 'if (!$_cb->extends) { ' . $this->macroInclude('#' . $name, $modifiers) . "; } {block $name}";
			}
		}
	}



	/**
	 * {/block}
	 */
	private function macroBlockEnd($content)
	{
		list($type, $name, $modifiers) = array_pop($this->blocks);

		if ($type === self::BLOCK_CAPTURE) { // capture - back compatibility
			$this->blocks[] = array($type, $name, $modifiers);
			return $this->macroCaptureEnd($content);
		}

		if (($type !== self::BLOCK_NAMED && $type !== self::BLOCK_ANONYMOUS) || ($content && $content !== $name)) {
			throw new /*\*/InvalidStateException("Tag {/block $content} was not expected here on line {$this->filter->line}.");

		} elseif ($type === self::BLOCK_NAMED) { // block
			return "{/block $name}";

		} else { // anonymous block
			return $modifiers === '' ? '' : 'echo ' . LatteFilter::formatModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * {snippet ...}
	 */
	private function macroSnippet($content)
	{
		$args = array('');
		if ($snippet = LatteFilter::fetchToken($content)) {  // [name [,]] [tag]
			$args[] = LatteFilter::formatString($snippet);
		}
		if ($content) {
			$args[] = LatteFilter::formatString($content);
		}
		return '} if ($_cb->foo = SnippetHelper::create($control' . implode(', ', $args) . ')) { $_cb->snippets[] = $_cb->foo';
	}



	/**
	 * {snippet ...}
	 */
	private function macroSnippetEnd($content)
	{
		return 'array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) {';
	}



	/**
	 * {capture ...}
	 */
	private function macroCapture($content, $modifiers)
	{
		$name = LatteFilter::fetchToken($content); // $variable

		if (substr($name, 0, 1) !== '$') {
			throw new /*\*/InvalidStateException("Invalid capture block parameter '$name' on line {$this->filter->line}.");
		}

		$this->blocks[] = array(self::BLOCK_CAPTURE, $name, $modifiers);
		return 'ob_start()';
	}



	/**
	 * {/capture}
	 */
	private function macroCaptureEnd($content)
	{
		list($type, $name, $modifiers) = array_pop($this->blocks);

		if ($type !== self::BLOCK_CAPTURE || ($content && $content !== $name)) {
			throw new /*\*/InvalidStateException("Tag {/capture $content} was not expected here on line {$this->filter->line}.");
		}

		return $name . '=' . LatteFilter::formatModifiers('ob_get_clean()', $modifiers);
	}



	/**
	 * Converts {block named}...{/block} to functions.
	 */
	private function cbNamedBlocks($matches)
	{
		list(, $name, $content) = $matches;
		$func = '_cbb' . substr(md5($this->uniq . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
		$this->namedBlocks[$name] = "//\n// block $name\n//\n"
			. "if (!function_exists(\$_cb->blocks[" . var_export($name, TRUE) . "][] = '$func')) { function $func() { extract(func_get_arg(0))\n?>$content<?php\n}}";
		return '';
	}



	/**
	 * {foreach ...}
	 */
	private function macroForeach($content)
	{
		return '$iterator = $_cb->its[] = new SmartCachingIterator(' . preg_replace('# +as +#i', ') as ', $content, 1);
	}



	/**
	 * {attr ...}
	 */
	private function macroAttr($content)
	{
		return preg_replace('#\)\s+#', ')->', $content . ' ');
	}



	/**
	 * {contentType ...}
	 */
	private function macroContentType($content)
	{
		if (strpos($content, 'html') !== FALSE) {
			$this->filter->escape = 'TemplateHelpers::escapeHtml';
			$this->filter->context = LatteFilter::CONTEXT_TEXT;

		} elseif (strpos($content, 'xml') !== FALSE) {
			$this->filter->escape = 'TemplateHelpers::escapeXml';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'javascript') !== FALSE) {
			$this->filter->escape = 'TemplateHelpers::escapeJs';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'css') !== FALSE) {
			$this->filter->escape = 'TemplateHelpers::escapeCss';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'plain') !== FALSE) {
			$this->filter->escape = '';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} else {
			$this->filter->escape = '$template->escape';
			$this->filter->context = LatteFilter::CONTEXT_NONE;
		}

		// temporary solution
		return strpos($content, '/') ? /*\Nette\*/'Environment::getHttpResponse()->setHeader("Content-Type", "' . $content . '")' : '';
	}



	/**
	 * {dump ...}
	 */
	private function macroDump($content)
	{
		return $content ? "array('$content' => $content)" : 'get_defined_vars()';
	}



	/**
	 * {widget ...}
	 */
	private function macroWidget($content)
	{
		$pair = LatteFilter::fetchToken($content); // widget[:method]
		if ($pair === NULL) {
			throw new /*\*/InvalidStateException("Missing widget name in {widget} on line {$this->filter->line}.");
		}
		$pair = explode(':', $pair, 2);
		$widget = LatteFilter::formatString($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = preg_match('#^('.LatteFilter::RE_IDENTIFIER.'|)$#', $method) ? "render$method" : "{\"render$method\"}";
		$param = LatteFilter::formatArray($content);
		if (strpos($content, '=>') === FALSE) $param = substr($param, 6, -1); // removes array()
		return ($widget[0] === '$' ? "if (is_object($widget)) {$widget}->$method($param); else " : '')
			. "\$control->getWidget($widget)->$method($param)";
	}



	/**
	 * {link ...}
	 */
	private function macroLink($content, $modifiers)
	{
		return LatteFilter::formatModifiers('$control->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	private function macroPlink($content, $modifiers)
	{
		return LatteFilter::formatModifiers('$presenter->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	private function macroIfCurrent($content)
	{
		return $content ? 'try { $presenter->link(' . $this->formatLink($content) . '); } catch (InvalidLinkException $e) {}' : '';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink($content)
	{
		return LatteFilter::formatString(LatteFilter::fetchToken($content)) . LatteFilter::formatArray($content, ', '); // destination [,] args
	}



	/**
	 * {assign ...}
	 */
	private function macroAssign($content, $modifiers)
	{
		if (!$content) {
			throw new /*\*/InvalidStateException("Missing arguments in {assign} on line {$this->filter->line}.");
		}
		if (strpos($content, '=>') === FALSE) { // back compatibility
			return '$' . ltrim(LatteFilter::fetchToken($content), '$') . ' = ' . LatteFilter::formatModifiers($content === '' ? 'NULL' : $content, $modifiers);
		}
		return 'extract(' . LatteFilter::formatArray($content) . ')';
	}



	/**
	 * {default ...}
	 */
	private function macroDefault($content)
	{
		if (!$content) {
			throw new /*\*/InvalidStateException("Missing arguments in {default} on line {$this->filter->line}.");
		}
		return 'extract(' . LatteFilter::formatArray($content) . ', EXTR_SKIP)';
	}



	/**
	 * Escaping helper.
	 */
	private function macroEscape($content)
	{
		return $this->filter->escape;
	}



	/**
	 * Just modifiers helper.
	 */
	private function macroModifiers($content, $modifiers)
	{
		return LatteFilter::formatModifiers($content, $modifiers);
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
	 * Includes subtemplate.
	 * @param  mixed      included file name or template
	 * @param  array      parameters
	 * @param  ITemplate  current template
	 * @return Template
	 */
	public static function includeTemplate($destination, $params, $template)
	{
		if ($destination instanceof ITemplate) {
			$tpl = $destination;

		} elseif ($destination == NULL) { // intentionally ==
			throw new /*\*/InvalidArgumentException("Template file name was not specified.");

		} else {
			$tpl = clone $template;
			if ($template instanceof IFileTemplate) {
				if (substr($destination, 0, 1) !== '/' && substr($destination, 1, 1) !== ':') {
					$destination = dirname($template->getFile()) . '/' . $destination;
				}
				$tpl->setFile($destination);
			}
		}

		$tpl->setParams($params); // interface?
		return $tpl;
	}



	/**
	 * Initializes state holder $_cb in template.
	 * @param  ITemplate
	 * @param  bool
	 * @param  string
	 * @return stdClass
	 */
	public static function initRuntime($template, $extends, $realFile)
	{
		$cb = (object) NULL;

		// extends support
		if (isset($template->_cb)) {
			$cb->blocks = & $template->_cb->blocks;
			$cb->templates = & $template->_cb->templates;
		}
		$cb->templates[$realFile] = $template;
		$cb->extends = is_bool($extends) ? $extends : (empty($template->_extends) ? FALSE : $template->_extends);
		unset($template->_cb, $template->_extends);

		// cache support
		if (!empty($cb->caches)) {
			end($cb->caches)->addFile($template->getFile());
		}

		return $cb;
	}

}
