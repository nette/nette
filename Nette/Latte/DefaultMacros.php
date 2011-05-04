<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Tokenizer;



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
 * - {ifset ?} ... {elseifset ?} ... {/ifset}
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
class DefaultMacros extends Nette\Object
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
		'/cache' => '<?php $_l->tmp = array_pop($_l->g->caches); if (!$_l->tmp instanceof \stdClass) $_l->tmp->end(); } ?>',

		'if' => '<?php if (%%): ?>',
		'elseif' => '<?php elseif (%%): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'ifset' => '<?php if (isset(%:macroIfset%)): ?>',
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
		'first' => '<?php if ($iterator->isFirst(%%)): ?>',
		'/first' => '<?php endif ?>',
		'last' => '<?php if ($iterator->isLast(%%)): ?>',
		'/last' => '<?php endif ?>',
		'sep' => '<?php if (!$iterator->isLast(%%)): ?>',
		'/sep' => '<?php endif ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',
		'layout' => '<?php %:macroExtends% ?>',

		'plink' => '<?php echo %:escape%(%:macroPlink%) ?>',
		'link' => '<?php echo %:escape%(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent% ?>', // deprecated; use n:class="$presenter->linkCurrent ? ..."
		'/ifCurrent' => '<?php endif ?>',
		'widget' => '<?php %:macroControl% ?>',
		'control' => '<?php %:macroControl% ?>',

		'@href' => ' href="<?php echo %:escape%(%:macroLink%) ?>"',
		'@class' => '<?php if ($_l->tmp = trim(implode(" ", array_unique(%:formatArray%)))) echo \' class="\' . %:escape%($_l->tmp) . \'"\' ?>',
		'@attr' => '<?php if (($_l->tmp = (string) (%%)) !== \'\') echo \' @@="\' . %:escape%($_l->tmp) . \'"\' ?>',

		'attr' => '<?php echo Nette\Utils\Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'status' => '<?php $netteHttpResponse->setCode(%%) ?>',
		'var' => '<?php %:macroVar% ?>',
		'assign' => '<?php %:macroVar% ?>', // deprecated
		'default' => '<?php %:macroDefault% ?>',
		'dump' => '<?php %:macroDump% ?>',
		'debugbreak' => '<?php %:macroDebugbreak% ?>',
		'l' => '{',
		'r' => '}',

		'!_' => '<?php echo %:macroTranslate% ?>',
		'_' => '<?php echo %:escape%(%:macroTranslate%) ?>',
		'!=' => '<?php echo %:macroModifiers% ?>',
		'=' => '<?php echo %:escape%(%:macroModifiers%) ?>',
		'!$' => '<?php echo %:macroDollar% ?>',
		'$' => '<?php echo %:escape%(%:macroDollar%) ?>',
		'?' => '<?php %:macroModifiers% ?>',
	);

	/** @internal PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @internal */
	const T_WHITESPACE = T_WHITESPACE,
		T_COMMENT = T_COMMENT,
		T_SYMBOL = -1,
		T_NUMBER = -2,
		T_VARIABLE = -3;

	/** @var Nette\Utils\Tokenizer */
	private $tokenizer;

	/** @var Parser */
	private $parser;

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

	/** @internal block type */
	const BLOCK_NAMED = 1,
		BLOCK_CAPTURE = 2,
		BLOCK_ANONYMOUS = 3;



	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->tokenizer = new Tokenizer(array(
			self::T_WHITESPACE => '\s+',
			self::T_COMMENT => '(?s)/\*.*?\*/',
			Parser::RE_STRING,
			'(?:true|false|null|and|or|xor|clone|new|instanceof|return|continue|break|[A-Z_][A-Z0-9_]{2,})(?![\d\pL_])', // keyword or const
			'\([a-z]+\)', // type casting
			self::T_VARIABLE => '\$[\d\pL_]+',
			self::T_NUMBER => '[+-]?[0-9]+(?:\.[0-9]+)?(?:e[0-9]+)?',
			self::T_SYMBOL => '[\d\pL_]+(?:-[\d\pL_]+)*',
			'::|=>|[^"\']', // =>, any char except quotes
		), 'u');
	}



	/**
	 * Initializes parsing.
	 * @param  Parser
	 * @return void
	 */
	public function initialize($parser)
	{
		$this->parser = $parser;
		$this->blocks = array();
		$this->namedBlocks = array();
		$this->extends = NULL;
		$this->uniq = Strings::random();
		$this->cacheCounter = 0;
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
			$s .= $this->parser->macro('/block');

		} elseif ($this->blocks) {
			throw new ParseException("There are unclosed blocks.", 0, $this->parser->line);
		}

		// extends support
		if ($this->namedBlocks || $this->extends) {
			$s = '<?php
if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return Nette\Latte\DefaultMacros::renderSnippets($control, $_l, get_defined_vars());
}
?>' . $s . '<?php
if ($_l->extends) {
	ob_end_clean();
	Nette\Latte\DefaultMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}
';
		} else {
			$s = '<?php
if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return Nette\Latte\DefaultMacros::renderSnippets($control, $_l, get_defined_vars());
}
?>' . $s;
		}

		// named blocks
		if ($this->namedBlocks) {
			$uniq = $this->uniq;
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$code = & $this->namedBlocks[$name];
				$namere = preg_quote($name, '#');
				$s = Strings::replace($s,
					"#{block $namere} \?>(.*)<\?php {/block $namere}#sU",
					function ($matches) use ($name, & $code, $uniq) {
						list(, $content) = $matches;
						$func = '_lb' . substr(md5($uniq . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
						$code = "//\n// block $name\n//\n"
							. "if (!function_exists(\$_l->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
							. "function $func(\$_l, \$_args) { "
							. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v') // PHP bug #46873
							. ($name[0] === '_' ? '; $control->validateControl(' . var_export(substr($name, 1), TRUE) . ')' : '') // snippet
							. "\n?>$content<?php\n}}";
						return '';
					}
				);
			}
			$s = "<?php\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n//\n// end of blocks\n//\n?>" . $s;
		}

		// internal state holder
		$s = "<?php\n"
			. '$_l = Nette\Latte\DefaultMacros::initRuntime($template, '
			. var_export($this->extends, TRUE) . ', ' . var_export($this->uniq, TRUE) . '); unset($_extends);'
			. "\n?>" . $s;
	}



	/********************* macros ****************d*g**/



	/**
	 * {$var |modifiers}
	 */
	public function macroDollar($var, $modifiers)
	{
		return $this->formatModifiers($this->formatMacroArgs('$' . $var), $modifiers);
	}



	/**
	 * {_$var |modifiers}
	 */
	public function macroTranslate($var, $modifiers)
	{
		return $this->formatModifiers($this->formatMacroArgs($var), '|translate' . $modifiers);
	}



	/**
	 * {syntax ...}
	 */
	public function macroSyntax($var)
	{
		switch ($var) {
		case '':
		case 'latte':
			$this->parser->setDelimiters('\\{(?![\\s\'"{}])', '\\}'); // {...}
			break;

		case 'double':
			$this->parser->setDelimiters('\\{\\{(?![\\s\'"{}])', '\\}\\}'); // {{...}}
			break;

		case 'asp':
			$this->parser->setDelimiters('<%\s*', '\s*%>'); /* <%...%> */
			break;

		case 'python':
			$this->parser->setDelimiters('\\{[{%]\s*', '\s*[%}]\\}'); // {% ... %} | {{ ... }}
			break;

		case 'off':
			$this->parser->setDelimiters('[^\x00-\xFF]', '');
			break;

		default:
			throw new ParseException("Unknown syntax '$var'", 0, $this->parser->line);
		}
	}



	/**
	 * {include ...}
	 */
	public function macroInclude($content, $modifiers, $isDefinition = FALSE)
	{
		$destination = $this->fetchToken($content); // destination [,] [params]
		$params = $this->formatArray($content) . ($content ? ' + ' : '');

		if ($destination === NULL) {
			throw new ParseException("Missing destination in {include}", 0, $this->parser->line);

		} elseif ($destination[0] === '#') { // include #block
			$destination = ltrim($destination, '#');
			if (!Strings::match($destination, '#^\$?' . self::RE_IDENTIFIER . '$#')) {
				throw new ParseException("Included block name must be alphanumeric string, '$destination' given.", 0, $this->parser->line);
			}

			$parent = $destination === 'parent';
			if ($destination === 'parent' || $destination === 'this') {
				$item = end($this->blocks);
				while ($item && $item[0] !== self::BLOCK_NAMED) $item = prev($this->blocks);
				if (!$item) {
					throw new ParseException("Cannot include $destination block outside of any block.", 0, $this->parser->line);
				}
				$destination = $item[1];
			}
			$name = $destination[0] === '$' ? $destination : var_export($destination, TRUE);
			$params .= $isDefinition ? 'get_defined_vars()' : '$template->getParams()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_l->blocks[$name]), \$_l, $params)"
				: 'Nette\Latte\DefaultMacros::callBlock' . ($parent ? 'Parent' : '') . "(\$_l, $name, $params)";
			return $modifiers
				? "ob_start(); $cmd; echo " . $this->formatModifiers('ob_get_clean()', $modifiers)
				: $cmd;

		} else { // include "file"
			$destination = $this->formatString($destination);
			$cmd = 'Nette\Latte\DefaultMacros::includeTemplate(' . $destination . ', '
				. $params . '$template->getParams(), $_l->templates[' . var_export($this->uniq, TRUE) . '])';
			return $modifiers
				? 'echo ' . $this->formatModifiers($cmd . '->__toString(TRUE)', $modifiers)
				: $cmd . '->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	public function macroExtends($content)
	{
		if (!$content) {
			throw new ParseException("Missing destination in {extends}", 0, $this->parser->line);
		}
		if (!empty($this->blocks)) {
			throw new ParseException("{extends} must be placed outside any block.", 0, $this->parser->line);
		}
		if ($this->extends !== NULL) {
			throw new ParseException("Multiple {extends} declarations are not allowed.", 0, $this->parser->line);
		}
		$this->extends = $content !== 'none';
		return $this->extends ? '$_l->extends = ' . ($content === 'auto' ? '$layout' : $this->formatMacroArgs($content)) : '';
	}



	/**
	 * {block ...}
	 */
	public function macroBlock($content, $modifiers)
	{
		$name = $this->fetchToken($content); // block [,] [params]

		if ($name === NULL) { // anonymous block
			$this->blocks[] = array(self::BLOCK_ANONYMOUS, NULL, $modifiers);
			return $modifiers === '' ? '' : 'ob_start()';

		} else { // #block
			$name = ltrim($name, '#');
			if (!Strings::match($name, '#^' . self::RE_IDENTIFIER . '$#')) {
				throw new ParseException("Block name must be alphanumeric string, '$name' given.", 0, $this->parser->line);

			} elseif (isset($this->namedBlocks[$name])) {
				throw new ParseException("Cannot redeclare block '$name'", 0, $this->parser->line);
			}

			$top = empty($this->blocks);
			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array(self::BLOCK_NAMED, $name, '');
			if ($name[0] === '_') { // snippet
				$tag = $this->fetchToken($content);  // [name [,]] [tag]
				$tag = trim($tag, '<>');
				$namePhp = var_export(substr($name, 1), TRUE);
				$tag = $tag ? $tag : 'div';
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

		} elseif ($type === self::BLOCK_NAMED) { // block
			return "{/block $name}";

		} else { // anonymous block
			return $modifiers === '' ? '' : 'echo ' . $this->formatModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * {snippet ...}
	 */
	public function macroSnippet($content)
	{
		return $this->macroBlock('_' . $content, '');
	}



	/**
	 * {snippet ...}
	 */
	public function macroSnippetEnd($content)
	{
		return $this->macroBlockEnd('', '');
	}



	/**
	 * {capture ...}
	 */
	public function macroCapture($content, $modifiers)
	{
		$name = $this->fetchToken($content); // $variable

		if (substr($name, 0, 1) !== '$') {
			throw new ParseException("Invalid capture block parameter '$name'", 0, $this->parser->line);
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
		return $name . '=' . $this->formatModifiers('ob_get_clean()', $modifiers);
	}



	/**
	 * {cache ...}
	 */
	public function macroCache($content)
	{
		return 'if (Nette\Latte\DefaultMacros::createCache($netteCacheStorage, '
			. var_export($this->uniq . ':' . $this->cacheCounter++, TRUE)
			. ', $_l->g->caches' . $this->formatArray($content, ', ') . ')) {';
	}



	/**
	 * {foreach ...}
	 */
	public function macroForeach($content)
	{
		return '$iterator = $_l->its[] = new Nette\Iterators\CachingIterator('
			. preg_replace('#(.*)\s+as\s+#i', '$1) as ', $this->formatMacroArgs($content), 1);
	}



	/**
	 * {ifset ...}
	 */
	public function macroIfset($content)
	{
		if (strpos($content, '#') === FALSE) {
			return $content;
		}
		$list = array();
		while (($name = $this->fetchToken($content)) !== NULL) {
			$list[] = $name[0] === '#' ? '$_l->blocks["' . substr($name, 1) . '"]' : $name;
		}
		return implode(', ', $list);
	}



	/**
	 * {attr ...}
	 */
	public function macroAttr($content)
	{
		return Strings::replace($content . ' ', '#\)\s+#', ')->');
	}



	/**
	 * {contentType ...}
	 */
	public function macroContentType($content)
	{
		if (strpos($content, 'html') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
			$this->parser->context = Parser::CONTEXT_TEXT;

		} elseif (strpos($content, 'xml') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeXml';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($content, 'javascript') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeJs';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($content, 'css') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeCss';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($content, 'plain') !== FALSE) {
			$this->parser->escape = '';
			$this->parser->context = Parser::CONTEXT_NONE;

		} else {
			$this->parser->escape = '$template->escape';
			$this->parser->context = Parser::CONTEXT_NONE;
		}

		// temporary solution
		if (strpos($content, '/')) {
			return '$netteHttpResponse->setHeader("Content-Type", "' . $content . '")';
		}
	}



	/**
	 * {dump ...}
	 */
	public function macroDump($content)
	{
		return 'Nette\Diagnostics\Debugger::barDump('
			. ($content ? 'array(' . var_export($this->formatMacroArgs($content), TRUE) . " => $content)" : 'get_defined_vars()')
			. ', "Template " . str_replace(dirname(dirname($template->getFile())), "\xE2\x80\xA6", $template->getFile()))';
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
		$pair = $this->fetchToken($content); // control[:method]
		if ($pair === NULL) {
			throw new ParseException("Missing control name in {control}", 0, $this->parser->line);
		}
		$pair = explode(':', $pair, 2);
		$name = $this->formatString($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = Strings::match($method, '#^(' . self::RE_IDENTIFIER . '|)$#') ? "render$method" : "{\"render$method\"}";
		$param = $this->formatArray($content);
		if (strpos($content, '=>') === FALSE) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_ctrl = $control->getWidget(' . $name . '); '
			. 'if ($_ctrl instanceof Nette\Application\UI\IPartiallyRenderable) $_ctrl->validateControl(); '
			. "\$_ctrl->$method($param)";
	}



	/**
	 * {link ...}
	 */
	public function macroLink($content, $modifiers)
	{
		return $this->formatModifiers('$control->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	public function macroPlink($content, $modifiers)
	{
		return $this->formatModifiers('$presenter->link(' . $this->formatLink($content) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	public function macroIfCurrent($content)
	{
		return ($content ? 'try { $presenter->link(' . $this->formatLink($content) . '); } catch (Nette\Application\UI\InvalidLinkException $e) {}' : '')
			. '; if ($presenter->getLastCreatedRequestFlag("current")):';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink($content)
	{
		return $this->formatString($this->fetchToken($content)) . $this->formatArray($content, ', '); // destination [,] args
	}



	/**
	 * {var ...}
	 */
	public function macroVar($content, $modifiers, $extract = FALSE)
	{
		$out = '';
		$var = TRUE;
		foreach ($this->parseMacro($content) as $token) {
			if ($var && ($token['type'] === self::T_SYMBOL || $token['type'] === self::T_VARIABLE)) {
				if ($extract) {
					$out .= "'" . trim($token['value'], "'$") . "'";
				} else {
					$out .= '$' . trim($token['value'], "'$");
				}
			} elseif (($token['value'] === '=' || $token['value'] === '=>') && $token['depth'] === 0) {
				$out .= $extract ? '=>' : '=';
				$var = FALSE;

			} elseif ($token['value'] === ',' && $token['depth'] === 0) {
				$out .= $extract ? ',' : ';';
				$var = TRUE;
			} else {
				$out .= $token['value'];
			}
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
	 * Just modifiers helper.
	 */
	public function macroModifiers($content, $modifiers)
	{
		return $this->formatModifiers($this->formatMacroArgs($content), $modifiers);
	}



	/**
	 * Escaping helper.
	 */
	public function escape($content)
	{
		return $this->parser->escape;
	}



	/********************* compile-time helpers ****************d*g**/



	/**
	 * Applies modifiers.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatModifiers($var, $modifiers)
	{
		if (!$modifiers) {
			return $var;
		}
		$inside = FALSE;
		foreach ($this->parseMacro(ltrim($modifiers, '|')) as $token) {
			if ($token['type'] === self::T_WHITESPACE) {
				$var = rtrim($var) . ' ';

			} elseif (!$inside) {
				if ($token['type'] === self::T_SYMBOL) {
					$var = "\$template->" . trim($token['value'], "'") . "($var";
					$inside = TRUE;
				} else {
					throw new ParseException("Modifier name must be alphanumeric string, '$token[value]' given.", 0, $this->parser->line);
				}
			} else {
				if ($token['value'] === ':' || $token['value'] === ',') {
					$var = $var . ', ';

				} elseif ($token['value'] === '|') {
					$var = $var . ')';
					$inside = FALSE;

				} else {
					$var .= $token['value'];
				}
			}
		}
		return $inside ? "$var)" : $var;
	}



	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public function fetchToken(& $s)
	{
		if ($matches = Strings::match($s, '#^((?>'.Parser::RE_STRING.'|[^\'"\s,]+)+)\s*,?\s*(.*)$#s')) { // token [,] tail
			$s = $matches[2];
			return $matches[1];
		}
		return NULL;
	}



	/**
	 * Reformats Latte to PHP code.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatMacroArgs($input)
	{
		$out = '';
		foreach ($this->parseMacro($input) as $token) {
			$out .= $token['value'];
		}
		return $out;
	}



	/**
	 * Reformats Latte to PHP array.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatArray($input, $prefix = '')
	{
		$tokens = $this->parseMacro($input);
		if (!$tokens) {
			return '';
		}
		$out = '';
		$expand = NULL;
		$tokens[] = NULL; // sentinel
		foreach ($tokens as $token) {
			if ($token['value'] === '(expand)' && $token['depth'] === 0) {
				$expand = TRUE;
				$out .= '),';

			} elseif ($expand && ($token['value'] === ',' || $token['value'] === NULL) && !$token['depth']) {
				$expand = FALSE;
				$out .= ', array(';
			} else {
				$out .= $token['value'];
			}
		}
		return $prefix . ($expand === NULL ? "array($out)" : "array_merge(array($out))");
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public function formatString($s)
	{
		static $keywords = array('true'=>1, 'false'=>1, 'null'=>1);
		return (is_numeric($s) || strspn($s, '\'"$') || isset($keywords[strtolower($s)])) ? $s : '"' . $s . '"';
	}



	/**
	 * Tokenizer and preparser.
	 * @return array
	 */
	private function parseMacro($input)
	{
		$this->tokenizer->tokenize($input);

		$inTernary = $lastSymbol = $prev = NULL;
		$tokens = $arrays = array();
		$n = -1;
		while (++$n < count($this->tokenizer->tokens)) {
			$token = $this->tokenizer->tokens[$n];
			$token['depth'] = $depth = count($arrays);

			if ($token['type'] === self::T_COMMENT) {
				continue; // remove comments

			} elseif ($token['type'] === self::T_WHITESPACE) {
				$tokens[] = $token;
				continue;

			} elseif ($token['type'] === self::T_SYMBOL && ($prev === NULL || in_array($prev['value'], array(',', '(', '[', '=', '=>', ':', '?')))) {
				$lastSymbol = count($tokens); // quoting pre-requirements

			} elseif (is_int($lastSymbol) && in_array($token['value'], array(',', ')', ']', '=', '=>', ':', '|'))) {
				$tokens[$lastSymbol]['value'] = "'" . $tokens[$lastSymbol]['value'] . "'"; // quote symbols
				$lastSymbol = NULL;

			} else {
				$lastSymbol = NULL;
			}

			if ($token['value'] === '?') { // short ternary operators without :
				$inTernary = $depth;

			} elseif ($token['value'] === ':') {
				$inTernary = NULL;

			} elseif ($inTernary === $depth && ($token['value'] === ',' || $token['value'] === ')' || $token['value'] === ']')) { // close ternary
				$tokens[] = Tokenizer::createToken(':') + array('depth' => $depth);
				$tokens[] = Tokenizer::createToken('null') + array('depth' => $depth);
				$inTernary = NULL;
			}

			if ($token['value'] === '[') { // simplified array syntax [...]
				if ($arrays[] = $prev['value'] !== ']' && $prev['type'] !== self::T_SYMBOL && $prev['type'] !== self::T_VARIABLE) {
					$tokens[] = Tokenizer::createToken('array') + array('depth' => $depth);
					$token = Tokenizer::createToken('(');
				}
			} elseif ($token['value'] === ']') {
				if (array_pop($arrays) === TRUE) {
					$token = Tokenizer::createToken(')');
				}
			} elseif ($token['value'] === '(') { // only count
				$arrays[] = '(';

			} elseif ($token['value'] === ')') { // only count
				array_pop($arrays);
			}

			$tokens[] = $prev = $token;
		}

		if (is_int($lastSymbol)) {
			$tokens[$lastSymbol]['value'] = "'" . $tokens[$lastSymbol]['value'] . "'"; // quote symbols
		}
		if ($inTernary !== NULL) { // close ternary
			$tokens[] = Tokenizer::createToken(':') + array('depth' => count($arrays));
			$tokens[] = Tokenizer::createToken('null') + array('depth' => count($arrays));
		}

		return $tokens;
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
			throw new Nette\InvalidStateException("Cannot include undefined block '$name'.");
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
			throw new Nette\InvalidStateException("Cannot include undefined parent block '$name'.");
		}
		$block($context, $params);
	}



	/**
	 * Includes subtemplate.
	 * @param  mixed      included file name or template
	 * @param  array      parameters
	 * @param  Nette\Templating\ITemplate  current template
	 * @return Nette\Templating\Template
	 */
	public static function includeTemplate($destination, $params, $template)
	{
		if ($destination instanceof Nette\Templating\ITemplate) {
			$tpl = $destination;

		} elseif ($destination == NULL) { // intentionally ==
			throw new Nette\InvalidArgumentException("Template file name was not specified.");

		} else {
			$tpl = clone $template;
			if ($template instanceof Nette\Templating\IFileTemplate) {
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
	 * @param  Nette\Templating\ITemplate
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
			end($local->g->caches)->dependencies[Nette\Caching\Cache::FILES][] = $template->getFile();
		}

		return $local;
	}



	public static function renderSnippets($control, $local, $params)
	{
		$payload = $control->getPresenter()->getPayload();
		if (isset($local->blocks)) {
			foreach ($local->blocks as $name => $function) {
				if ($name[0] !== '_' || !$control->isControlInvalid(substr($name, 1))) {
					continue;
				}
				ob_start();
				$function = reset($function);
				$function($local, $params);
				$payload->snippets[$control->getSnippetId(substr($name, 1))] = ob_get_clean();
			}
		}
		if ($control instanceof Nette\Application\UI\Control) {
			foreach ($control->getComponents(FALSE, 'Nette\Application\UI\Control') as $child) {
				if ($child->isControlInvalid()) {
					$child->render();
				}
			}
		}
	}



	/**
	 * Starts the output cache. Returns Nette\Caching\OutputHelper object if buffering was started.
	 * @param  Nette\Caching\IStorage
	 * @param  string
	 * @param  array of Nette\Caching\OutputHelper
	 * @param  array
	 * @return Nette\Caching\OutputHelper
	 */
	public static function createCache(Nette\Caching\IStorage $cacheStorage, $key, & $parents, $args = NULL)
	{
		if ($args) {
			if (array_key_exists('if', $args) && !$args['if']) {
				return $parents[] = (object) NULL;
			}
			$key = array_merge(array($key), array_intersect_key($args, range(0, count($args))));
		}
		if ($parents) {
			end($parents)->dependencies[Nette\Caching\Cache::ITEMS][] = $key;
		}

		$cache = new Nette\Caching\Cache($cacheStorage, 'Nette.Templating.Cache');
		if ($helper = $cache->start($key)) {
			$helper->dependencies = array(
				Nette\Caching\Cache::TAGS => isset($args['tags']) ? $args['tags'] : NULL,
				Nette\Caching\Cache::EXPIRATION => isset($args['expire']) ? $args['expire'] : '+ 7 days',
			);
			$parents[] = $helper;
		}
		return $helper;
	}

}
