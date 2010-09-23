<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Templates;

use Nette,
	Nette\String,
	Nette\Tokenizer;



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
 * - {var var => value} set template parameter
 * - {default var => value} set default template parameter
 * - {dump $var}
 * - {debugbreak}
 * - {l} {r} to display { }
 *
 * @author     David Grudl
 */
class LatteMacros extends Nette\Object
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

		'cache' => '<?php %:macroCache% ?>',
		'/cache' => '<?php array_pop($_l->g->caches)->save(); } ?>',

		'if' => '<?php if (%%): ?>',
		'elseif' => '<?php elseif (%%): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'ifset' => '<?php if (isset(%%)): ?>',
		'/ifset' => '<?php endif ?>',
		'elseifset' => '<?php elseif (isset(%%)): ?>',
		'foreach' => '<?php foreach (%:macroForeach%): ?>',
		'/foreach' => '<?php endforeach; array_pop($_l->its); $iterator = end($_l->its) ?>',
		'for' => '<?php for (%%): ?>',
		'/for' => '<?php endfor ?>',
		'while' => '<?php while (%%): ?>',
		'/while' => '<?php endwhile ?>',
		'continueIf' => '<?php if (%%) continue ?>',
		'breakIf' => '<?php if (%%) break ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',
		'layout' => '<?php %:macroExtends% ?>',

		'plink' => '<?php echo %:macroEscape%(%:macroPlink%) ?>',
		'link' => '<?php echo %:macroEscape%(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent% ?>',
		'widget' => '<?php %:macroControl% ?>',
		'control' => '<?php %:macroControl% ?>',

		'attr' => '<?php echo Nette\Web\Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'status' => '<?php Nette\Environment::getHttpResponse()->setCode(%%) ?>',
		'var' => '<?php %:macroVar% ?>',
		'assign' => '<?php %:macroVar% ?>', // deprecated
		'default' => '<?php %:macroDefault% ?>',
		'dump' => '<?php %:macroDump% ?>',
		'debugbreak' => '<?php %:macroDebugbreak% ?>',
		'l' => '{',
		'r' => '}',

		'!_' => '<?php echo %:macroTranslate% ?>',
		'_' => '<?php echo %:macroEscape%(%:macroTranslate%) ?>',
		'!=' => '<?php echo %:macroModifiers% ?>',
		'=' => '<?php echo %:macroEscape%(%:macroModifiers%) ?>',
		'!$' => '<?php echo %:macroDollar% ?>',
		'$' => '<?php echo %:macroEscape%(%:macroDollar%) ?>',
		'?' => '<?php %:macroModifiers% ?>',
	);

	/** @internal PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @internal */
	const T_SYMBOL = -1;

	/** @var array */
	public $macros;

	/** @var LatteFilter */
	private $filter;

	/** @var array */
	private $blocks = array();

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;

	/** @var string */
	private $uniq;

	/** @var int */
	private $cacheCounter;

	/** @var bool */
	private $oldSnippetMode = TRUE;

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
		$this->uniq = substr(md5(uniqid('', TRUE)), 0, 10);
		$this->cacheCounter = 0;

		$filter->context = LatteFilter::CONTEXT_TEXT;
		$filter->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';

		// remove comments
		$s = String::replace($s, '#\\{\\*.*?\\*\\}[\r\n]*#s', '');

		// snippets support (temporary solution)
		$s = String::replace(
			$s,
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (Nette\Templates\SnippetHelper::$outputAllowed) { ?>'
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
			throw new \InvalidStateException("There are some unclosed blocks.");
		}

		// snippets support (temporary solution)
		$s = "<?php\nif (" . 'Nette\Templates\SnippetHelper::$outputAllowed' . ") {\n?>$s<?php\n}\n?>";

		// extends support
		if ($this->namedBlocks || $this->extends) {
			$s = "<?php\n"
				. 'if ($_l->extends) { ob_start(); }' . "\n"
				. 'elseif (isset($presenter, $control) && $presenter->isAjax()) { Nette\Templates\LatteMacros::renderSnippets($control, $_l, get_defined_vars()); }' . "\n"
				. '?>' . $s . "<?php\n"
				. 'if ($_l->extends) { ob_end_clean(); Nette\Templates\LatteMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render(); }' . "\n";
		} else {
			$s = "<?php\n"
				. 'if (isset($presenter, $control) && $presenter->isAjax()) { Nette\Templates\LatteMacros::renderSnippets($control, $_l, get_defined_vars()); }' . "\n"
				. '?>' . $s;
		}

		// named blocks
		if ($this->namedBlocks) {
			$uniq = $this->uniq;
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$code = & $this->namedBlocks[$name];
				$namere = preg_quote($name, '#');
				$s = String::replace($s,
					"#{block $namere} \?>(.*)<\?php {/block $namere}#sU",
					function ($matches) use ($name, & $code, $uniq) {
						list(, $content) = $matches;
						$func = '_lb' . substr(md5($uniq . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
						$code = "//\n// block $name\n//\n"
							. "if (!function_exists(\$_l->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
							. "function $func(\$_l, \$_args) { extract(\$_args)\n?>$content<?php\n}}";
						return '';
					}
				);
			}
			$s = "<?php\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n//\n// end of blocks\n//\n?>" . $s;
		}

		// internal state holder
		$s = "<?php\n"
			. '$_l = Nette\Templates\LatteMacros::initRuntime($template, ' . var_export($this->extends, TRUE) . ', ' . var_export($this->uniq, TRUE) . '); unset($_extends);'
			. "\n?>" . $s;
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
		$This = $this;
		return String::replace(
			$this->macros[$macro],
			'#%(.*?)%#',
			function ($m) use ($This, $content, $modifiers) {
				if ($m[1]) {
					return callback($m[1][0] === ':' ? array($This, substr($m[1], 1)) : $m[1])
						->invoke($content, $modifiers);
				} else {
					return $content;
				}
			}
		);
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
			isset($knownTags[$name], $attrs[$knownTags[$name]])
				? $attrs[$knownTags[$name]]
				: preg_replace("#'([^\\'$]+)'#", '$1', substr(var_export($attrs, TRUE), 8, -1)),
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
	public function macroDollar($var, $modifiers)
	{
		return self::formatModifiers('$' . $var, $modifiers);
	}



	/**
	 * {_$var |modifiers}
	 */
	public function macroTranslate($var, $modifiers)
	{
		return self::formatModifiers($var, '|translate' . $modifiers);
	}



	/**
	 * {syntax ...}
	 */
	public function macroSyntax($var)
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
			$this->filter->setDelimiters('<%\s*', '\s*%>'); /* <%...%> */
			break;

		case 'python':
			$this->filter->setDelimiters('\\{[{%]\s*', '\s*[%}]\\}'); // {% ... %} | {{ ... }}
			break;

		case 'off':
			$this->filter->setDelimiters('[^\x00-\xFF]', '');
			break;

		default:
			throw new \InvalidStateException("Unknown macro syntax '$var' on line {$this->filter->line}.");
		}
	}



	/**
	 * {include ...}
	 */
	public function macroInclude($content, $modifiers, $isDefinition = FALSE)
	{
		$destination = self::fetchToken($content); // destination [,] [params]
		$params = self::formatArray($content) . ($content ? ' + ' : '');

		if ($destination === NULL) {
			throw new \InvalidStateException("Missing destination in {include} on line {$this->filter->line}.");

		} elseif ($destination[0] === '#') { // include #block
			$destination = ltrim($destination, '#');
			if (!String::match($destination, '#^' . self::RE_IDENTIFIER . '$#')) {
				throw new \InvalidStateException("Included block name must be alphanumeric string, '$destination' given on line {$this->filter->line}.");
			}

			$parent = $destination === 'parent';
			if ($destination === 'parent' || $destination === 'this') {
				$item = end($this->blocks);
				while ($item && $item[0] !== self::BLOCK_NAMED) $item = prev($this->blocks);
				if (!$item) {
					throw new \InvalidStateException("Cannot include $destination block outside of any block on line {$this->filter->line}.");
				}
				$destination = $item[1];
			}
			$name = var_export($destination, TRUE);
			$params .= $isDefinition ? 'get_defined_vars()' : '$template->getParams()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_l->blocks[$name]), \$_l, $params)"
				: 'Nette\Templates\LatteMacros::callBlock' . ($parent ? 'Parent' : '') . "(\$_l, $name, $params)";
			return $modifiers
				? "ob_start(); $cmd; echo " . self::formatModifiers('ob_get_clean()', $modifiers)
				: $cmd;

		} else { // include "file"
			$destination = self::formatString($destination);
			$cmd = 'Nette\Templates\LatteMacros::includeTemplate(' . $destination . ', ' . $params . '$template->getParams(), $_l->templates[' . var_export($this->uniq, TRUE) . '])';
			return $modifiers
				? 'echo ' . self::formatModifiers($cmd . '->__toString(TRUE)', $modifiers)
				: $cmd . '->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	public function macroExtends($content)
	{
		$destination = self::fetchToken($content); // destination
		if ($destination === NULL) {
			throw new \InvalidStateException("Missing destination in {extends} on line {$this->filter->line}.");
		}
		if (!empty($this->blocks)) {
			throw new \InvalidStateException("{extends} must be placed outside any block; on line {$this->filter->line}.");
		}
		if ($this->extends !== NULL) {
			throw new \InvalidStateException("Multiple {extends} declarations are not allowed; on line {$this->filter->line}.");
		}
		$this->extends = $destination !== 'none';
		return $this->extends ? '$_l->extends = ' . self::formatString($destination) : '';
	}



	/**
	 * {block ...}
	 */
	public function macroBlock($content, $modifiers)
	{
		$name = self::fetchToken($content); // block [,] [params]

		if ($name === NULL) { // anonymous block
			$this->blocks[] = array(self::BLOCK_ANONYMOUS, NULL, $modifiers);
			return $modifiers === '' ? '' : 'ob_start()';

		} else { // #block
			$name = ltrim($name, '#');
			if (!String::match($name, '#^' . self::RE_IDENTIFIER . '$#')) {
				throw new \InvalidStateException("Block name must be alphanumeric string, '$name' given on line {$this->filter->line}.");

			} elseif (isset($this->namedBlocks[$name])) {
				throw new \InvalidStateException("Cannot redeclare block '$name'; on line {$this->filter->line}.");
			}

			$top = empty($this->blocks);
			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array(self::BLOCK_NAMED, $name, '');
			if ($name[0] === '_') { // snippet - experimental
				$tag = self::fetchToken($content);  // [name [,]] [tag]
				$tag = trim($tag, '<>');
				$namePhp = var_export(substr($name, 1), TRUE);
				if (!$tag) $tag = 'div';
				return "?><$tag id=\"<?php echo \$control->getSnippetId($namePhp) ?>\"><?php "
					. $this->macroInclude('#' . $name, $modifiers)
					. " ?></$tag><?php {block $name}";

			} elseif (!$top) {
				return $this->macroInclude('#' . $name, $modifiers, TRUE) . "{block $name}";

			} elseif ($this->extends) {
				return "{block $name}";

			} else {
				return 'if (!$_l->extends) { ' . $this->macroInclude('#' . $name, $modifiers, TRUE) . "; } {block $name}";
			}
		}
	}



	/**
	 * {/block}
	 */
	public function macroBlockEnd($content)
	{
		list($type, $name, $modifiers) = array_pop($this->blocks);

		if ($type === self::BLOCK_CAPTURE) { // capture - back compatibility
			$this->blocks[] = array($type, $name, $modifiers);
			return $this->macroCaptureEnd($content);
		}

		if (($type !== self::BLOCK_NAMED && $type !== self::BLOCK_ANONYMOUS) || ($content && $content !== $name)) {
			throw new \InvalidStateException("Tag {/block $content} was not expected here on line {$this->filter->line}.");

		} elseif ($type === self::BLOCK_NAMED) { // block
			return "{/block $name}";

		} else { // anonymous block
			return $modifiers === '' ? '' : 'echo ' . self::formatModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * {snippet ...}
	 */
	public function macroSnippet($content)
	{
		if (substr($content, 0, 1) === ':' || !$this->oldSnippetMode) { // experimental behaviour
			$this->oldSnippetMode = FALSE;
			return $this->macroBlock('_' . ltrim($content, ':'), '');
		}
		$args = array('');
		if ($snippet = self::fetchToken($content)) {  // [name [,]] [tag]
			$args[] = self::formatString($snippet);
		}
		if ($content) {
			$args[] = self::formatString($content);
		}
		return '} if ($_l->foo = Nette\Templates\SnippetHelper::create($control' . implode(', ', $args) . ')) { $_l->snippets[] = $_l->foo';
	}



	/**
	 * {snippet ...}
	 */
	public function macroSnippetEnd($content)
	{
		if (!$this->oldSnippetMode) {
			return $this->macroBlockEnd('', '');
		}
		return 'array_pop($_l->snippets)->finish(); } if (Nette\Templates\SnippetHelper::$outputAllowed) {';
	}



	/**
	 * {capture ...}
	 */
	public function macroCapture($content, $modifiers)
	{
		$name = self::fetchToken($content); // $variable

		if (substr($name, 0, 1) !== '$') {
			throw new \InvalidStateException("Invalid capture block parameter '$name' on line {$this->filter->line}.");
		}

		$this->blocks[] = array(self::BLOCK_CAPTURE, $name, $modifiers);
		return 'ob_start()';
	}



	/**
	 * {/capture}
	 */
	public function macroCaptureEnd($content)
	{
		list($type, $name, $modifiers) = array_pop($this->blocks);

		if ($type !== self::BLOCK_CAPTURE || ($content && $content !== $name)) {
			throw new \InvalidStateException("Tag {/capture $content} was not expected here on line {$this->filter->line}.");
		}

		return $name . '=' . self::formatModifiers('ob_get_clean()', $modifiers);
	}



	/**
	 * {cache ...}
	 */
	public function macroCache($content)
	{
		return 'if (Nette\Templates\CachingHelper::create('
			. var_export($this->uniq . ':' . $this->cacheCounter++, TRUE)
			. ', $_l->g->caches' . self::formatArray($content, ', ') . ')) {';
	}



	/**
	 * {foreach ...}
	 */
	public function macroForeach($content)
	{
		return '$iterator = $_l->its[] = new Nette\SmartCachingIterator(' . preg_replace('# +as +#i', ') as ', $content, 1);
	}



	/**
	 * {attr ...}
	 */
	public function macroAttr($content)
	{
		return String::replace($content . ' ', '#\)\s+#', ')->');
	}



	/**
	 * {contentType ...}
	 */
	public function macroContentType($content)
	{
		if (strpos($content, 'html') !== FALSE) {
			$this->filter->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
			$this->filter->context = LatteFilter::CONTEXT_TEXT;

		} elseif (strpos($content, 'xml') !== FALSE) {
			$this->filter->escape = 'Nette\Templates\TemplateHelpers::escapeXml';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'javascript') !== FALSE) {
			$this->filter->escape = 'Nette\Templates\TemplateHelpers::escapeJs';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'css') !== FALSE) {
			$this->filter->escape = 'Nette\Templates\TemplateHelpers::escapeCss';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} elseif (strpos($content, 'plain') !== FALSE) {
			$this->filter->escape = '';
			$this->filter->context = LatteFilter::CONTEXT_NONE;

		} else {
			$this->filter->escape = '$template->escape';
			$this->filter->context = LatteFilter::CONTEXT_NONE;
		}

		// temporary solution
		return strpos($content, '/') ? 'Nette\Environment::getHttpResponse()->setHeader("Content-Type", "' . $content . '")' : '';
	}



	/**
	 * {dump ...}
	 */
	public function macroDump($content)
	{
		return 'Nette\Debug::barDump('
			. ($content ? 'array(' . var_export($content, TRUE) . " => $content)" : 'get_defined_vars()')
			. ', "Template " . str_replace(Nette\Environment::getVariable("appDir", ""), "\xE2\x80\xA6", $template->getFile()))';
	}



	/**
	 * {debugbreak}
	 */
	public function macroDebugbreak()
	{
		return 'if (function_exists("debugbreak")) debugbreak(); elseif (function_exists("xdebug_break")) xdebug_break()';
	}



	/**
	 * {control ...}
	 */
	public function macroControl($content)
	{
		$pair = self::fetchToken($content); // control[:method]
		if ($pair === NULL) {
			throw new \InvalidStateException("Missing control name in {control} on line {$this->filter->line}.");
		}
		$pair = explode(':', $pair, 2);
		$name = self::formatString($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = String::match($method, '#^(' . self::RE_IDENTIFIER . '|)$#') ? "render$method" : "{\"render$method\"}";
		$param = self::formatArray($content);
		if (strpos($content, '=>') === FALSE) $param = substr($param, 6, -1); // removes array()
		return ($name[0] === '$' ? "if (is_object($name)) {$name}->$method($param); else " : '')
			. "\$control->getWidget($name)->$method($param)";
	}



	/**
	 * {link ...}
	 */
	public function macroLink($content, $modifiers)
	{
		return self::formatModifiers('$control->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	public function macroPlink($content, $modifiers)
	{
		return self::formatModifiers('$presenter->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	public function macroIfCurrent($content)
	{
		return ($content ? 'try { $presenter->link(' . $this->formatLink($content) . '); } catch (Nette\Application\InvalidLinkException $e) {}' : '')
			. '; if ($presenter->getLastCreatedRequestFlag("current")):';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink($content)
	{
		return self::formatString(self::fetchToken($content)) . self::formatArray($content, ', '); // destination [,] args
	}



	/**
	 * {var ...}
	 */
	public function macroVar($content, $modifiers, $extract = FALSE)
	{
		$tokenizer = new Tokenizer(array(
			Tokenizer::T_WHITESPACE => '\s+',
			Tokenizer::RE_STRING,
			'true|false|null|and|or|xor|clone|new|instanceof',
			self::T_SYMBOL => '\$?[0-9a-zA-Z]+', // variable, string, number
			'=>|[^"\']', // =>, any char except quotes
		), 'i');

		$out = '';
		$quote = $var = TRUE;
		$depth = 0;
		foreach ($tokenizer->tokenize($content) as $n => $token) {
			list($token, $name) = $token;

			if ($name === self::T_SYMBOL) {
				if ($var) {
					if ($extract) {
						$token = "'" . ($token[0] === '$' ? substr($token, 1) : $token) . "'";
					} else {
						$token = $token[0] === '$' ? $token : '$' . $token;
		}
				} elseif ($quote && $token[0] !== '$' && !is_numeric($token) && in_array($tokenizer->nextToken($n), array(',', '=>', ')', NULL), TRUE)) {
					$token = "'$token'";
		}
			} elseif ($token === '(') {
				$depth++;

			} elseif ($token === ')') {
				$depth--;

			} elseif (($token === '=' || $token === '=>') && $depth === 0) {
				$token = $extract ? '=>' : '=';
				$var = FALSE;

			} elseif ($token === ',' && $depth === 0) {
				$token = $extract ? ',' : ';';
				$var = TRUE;
	}

			if ($name !== Tokenizer::T_WHITESPACE) {
				$quote = in_array($token, array('[', ',', '=', '(', '=>'));
			}
			$out .= $token;
		}
		return $out;
	}



	/**
	 * {default ...}
	 */
	public function macroDefault($content)
	{
		return 'extract(array(' . $this->macroVar($content, '', TRUE) . '), EXTR_SKIP)';
		}



	/**
	 * Escaping helper.
	 */
	public function macroEscape($content)
	{
		return $this->filter->escape;
	}



	/**
	 * Just modifiers helper.
	 */
	public function macroModifiers($content, $modifiers)
	{
		return self::formatModifiers($content, $modifiers);
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
		$tokenizer = new Tokenizer(array(
			Tokenizer::T_WHITESPACE => '\s+',
			Tokenizer::RE_STRING,
			'true|false|null|and|or|xor|clone|new|instanceof',
			'\$[_a-z0-9\x7F-\xFF]+', // variable
			self::T_SYMBOL => '[_a-z0-9\x7F-\xFF]+', // string, number
			'[^"\']', // =>, any char except quotes
		), 'i');

		$inside = FALSE;
		foreach ($tokenizer->tokenize(ltrim($modifiers, '|')) as $n => $token) {
			list($token, $name) = $token;
			if ($name === Tokenizer::T_WHITESPACE) {
				$var = rtrim($var) . ' ';

			} elseif (!$inside) {
				if ($name === self::T_SYMBOL) {
					$var = "\$template->$token($var";
					$inside = TRUE;
				} else {
					throw new \InvalidStateException("Modifier name must be alphanumeric string, '$token' given.");
				}
			} else {
				if ($token === ':' || $token === ',') {
					$var = $var . ', ';

				} elseif ($token === '|') {
					$var = $var . ')';
					$inside = FALSE;

				} elseif ($name === self::T_SYMBOL && $quote && !is_numeric($token)
					&& in_array($tokenizer->nextToken($n), array(',', ':', ')', '|', NULL), TRUE)) {
					$var .= "'$token'";

				} else {
					$var .= $token;
				}
			}
			if ($name !== Tokenizer::T_WHITESPACE) {
				$quote = in_array($token, array('[', ',', '=', '(', ':'));
			}
		}
		return $inside ? "$var)" : $var;
	}



	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public static function fetchToken(& $s)
	{
		if ($matches = String::match($s, '#^((?>'.Tokenizer::RE_STRING.'|[^\'"\s,]+)+)\s*,?\s*(.*)$#')) { // token [,] tail
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
	public static function formatArray($input, $prefix = '')
	{
		$tokenizer = new Tokenizer(array(
			Tokenizer::T_WHITESPACE => '\s+',
			Tokenizer::T_COMMENT => '/\*.*?\*/',
			Tokenizer::RE_STRING,
			'true|false|null|and|or|xor|clone|new|instanceof',
			'\$[_a-z0-9\x7F-\xFF]+', // variable
			self::T_SYMBOL => '[_a-z0-9\x7F-\xFF]+', // string, number
			'=>|[^"\']', // =>, any char except quotes
		), 'i');

		$out = '';
		$quote = TRUE;
		foreach ($tokenizer->tokenize($input) as $n => $token) {
			list($token, $name) = $token;

			if ($name === Tokenizer::T_COMMENT) {
				continue;

			} elseif ($name === self::T_SYMBOL && $quote && !is_numeric($token)
				&& in_array($tokenizer->nextToken($n), array(',', '=>', ')', NULL), TRUE)) {
				$token = "'$token'";
			}
			if ($name !== Tokenizer::T_WHITESPACE) {
				$quote = in_array($token, array('[', ',', '=', '(', '=>'));
			}
			$out .= $token;
		}
		return $out === '' ? '' : $prefix . "array($out)";
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public static function formatString($s)
	{
		static $keywords = array('true'=>1, 'false'=>1, 'null'=>1);
		return (is_numeric($s) || strspn($s, '\'"$') || isset($keywords[strtolower($s)])) ? $s : '"' . $s . '"';
	}



	/********************* run-time helpers ****************d*g**/



	/**
	 * Calls block.
	 * @param  stdClass
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlock($context, $name, $params)
	{
		if (empty($context->blocks[$name])) {
			throw new \InvalidStateException("Call to undefined block '$name'.");
		}
		$block = reset($context->blocks[$name]);
		$block($context, $params);
	}



	/**
	 * Calls parent block.
	 * @param  stdClass
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlockParent($context, $name, $params)
	{
		if (empty($context->blocks[$name]) || ($block = next($context->blocks[$name])) === FALSE) {
			throw new \InvalidStateException("Call to undefined parent block '$name'.");
		}
		$block($context, $params);
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
			throw new \InvalidArgumentException("Template file name was not specified.");

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
	 * Initializes local & global storage in template.
	 * @param  ITemplate
	 * @param  bool
	 * @param  string
	 * @return stdClass
	 */
	public static function initRuntime($template, $extends, $realFile)
	{
		$local = (object) NULL;

		// extends support
		if (isset($template->_l)) {
			$local->blocks = & $template->_l->blocks;
			$local->templates = & $template->_l->templates;
		}
		$local->templates[$realFile] = $template;
		$local->extends = is_bool($extends) ? $extends : (empty($template->_extends) ? FALSE : $template->_extends);
		unset($template->_l, $template->_extends);

		// global storage
		if (!isset($template->_g)) {
			$template->_g = (object) NULL;
		}
		$local->g = $template->_g;

		// cache support
		if (!empty($local->g->caches)) {
			end($local->g->caches)->addFile($template->getFile());
		}

		return $local;
	}



	public static function renderSnippets($control, $local, $params)
	{
		$payload = $control->getPresenter()->getPayload();
		if (isset($local->blocks)) {
			foreach ($local->blocks as $name => $function) {
				if ($name[0] !== '_' || !$control->isControlInvalid(substr($name, 1))) continue;
				ob_start();
				$function = reset($function);
				$function($local, $params);
				$payload->snippets[$control->getSnippetId(substr($name, 1))] = ob_get_clean();
			}
		}
	}

}
