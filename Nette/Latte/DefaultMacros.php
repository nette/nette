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
	Nette\Utils\Strings;



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
class DefaultMacros extends Nette\Object implements IMacro
{
	/** @var array */
	public static $defaultMacros = array(
		'syntax' => '%:macroSyntax%',
		'/syntax' => '%:macroSyntax%',

		'block' => '<?php %:macroBlock% ?>',
		'/block' => '<?php %:macroBlockEnd% ?>',

		'capture' => '<?php %:macroCapture% ?>',
		'/capture' => '<?php %:macroCaptureEnd% ?>',

		'snippet' => '<?php %:macroBlock% ?>',
		'/snippet' => '<?php %:macroBlockEnd% ?>',

		'cache' => '<?php %:macroCache% ?>',
		'/cache' => '<?php $_l->tmp = array_pop($_g->caches); if (!$_l->tmp instanceof \stdClass) $_l->tmp->end(); } ?>',

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

		'plink' => '<?php echo %:escape%(%:macroLink%) ?>',
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
		'default' => '<?php %:macroVar% ?>',
		'dump' => '<?php %:macroDump% ?>',
		'debugbreak' => '<?php %:macroDebugbreak% ?>',
		'l' => '{',
		'r' => '}',

		'_' => '<?php echo %:macroTranslate% ?>',
		'=' => '<?php echo %:macroModifiers% ?>',
		'?' => '<?php %:macroModifiers% ?>',
	);

	/** @internal PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @var Parser */
	public $parser;

	/** @var PhpWriter */
	public $writer;

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;

	/** @var int */
	private $cacheCounter;



	public static function install(Parser $parser)
	{
		$me = new static;
		$me->parser = $parser;
		$me->writer = new PhpWriter;

		foreach (self::$defaultMacros as $name => $foo) {
			if ($name[0] !== '/') {
				$parser->addMacro($name, $me);
			}
		}
	}



	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->namedBlocks = array();
		$this->extends = NULL;
		$this->cacheCounter = 0;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		// try close last block
		try {
			$this->parser->writeMacro('/block');
		} catch (ParseException $e) {
		}

		// internal state holder
		$epilog = $prolog = array();
		$prolog[] = 'list($_l, $_g) = Nette\Latte\DefaultMacros::initRuntime($template, '
			. var_export($this->extends, TRUE) . ', ' . var_export($this->parser->templateId, TRUE) . '); unset($_extends);';

		// named blocks
		if ($this->namedBlocks) {
			$prolog[] = '';
			foreach ($this->namedBlocks as $name => $code) {
				$func = '_lb' . substr(md5($this->parser->templateId . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				$prolog[] = "//\n// block $name\n//\n"
					. "if (!function_exists(\$_l->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
					. "function $func(\$_l, \$_args) { "
					. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v') // PHP bug #46873
					. ($name[0] === '_' ? '; $control->validateControl(' . var_export(substr($name, 1), TRUE) . ')' : '') // snippet
					. "\n?>$code<?php\n}}\n\n";
			}
			$prolog[] = "//\n// end of blocks\n//";
		}

		// extends support
		if ($this->namedBlocks || $this->extends) {
			$prolog[] = '
if ($_l->extends) {
	ob_start();
} elseif (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return Nette\Latte\DefaultMacros::renderSnippets($control, $_l, get_defined_vars());
}';
			$epilog[] = '
if ($_l->extends) {
	ob_end_clean();
	Nette\Latte\DefaultMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}';
		} else {
			$prolog[] = '
if (isset($presenter, $control) && $presenter->isAjax() && $control->isControlInvalid()) {
	return Nette\Latte\DefaultMacros::renderSnippets($control, $_l, get_defined_vars());
}';
		}

		return array(implode("\n", $prolog), implode("\n", $epilog));
	}



	/**
	 * New node is found. Returns FALSE to reject or code.
	 * @return bool|string
	 */
	public function nodeOpened(MacroNode $node)
	{
		$node->isEmpty = !isset(self::$defaultMacros["/$node->name"]);
		return $this->compile($node, $node->name);
	}



	/**
	 * Node is closed. Returns code.
	 * @return string
	 */
	public function nodeClosed(MacroNode $node)
	{
		return $this->compile($node, "/$node->name");
	}



	/**
	 * @return string
	 */
	private function compile(MacroNode $node, $name)
	{
		$me = $this;
		$code = Strings::replace(
			self::$defaultMacros[$name],
			'#%(.*?)%#',
			/*5.2* callback(*/function ($m) use ($me, $node) {
				if ($m[1]) {
					return callback($m[1][0] === ':' ? array($me, substr($m[1], 1)) : $m[1])
						->invoke($node);
				} else {
					return $me->writer->formatArgs($node->args);
				}
			}/*5.2* )*/
		);
		return $code;
	}



	/********************* macros ****************d*g**/



	/**
	 * {_$var |modifiers}
	 */
	public function macroTranslate(MacroNode $node)
	{
		return $this->writer->formatModifiers($this->writer->formatArgs($node->args), '|translate' . $node->modifiers, $this->parser->escape);
	}



	/**
	 * {syntax ...}
	 */
	public function macroSyntax(MacroNode $node)
	{
		if ($node->closing) {
			$node->args = 'latte';
		}
		switch ($node->args) {
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
			throw new ParseException("Unknown syntax '$node->args'", 0, $this->parser->line);
		}
	}



	/**
	 * {include ...}
	 */
	public function macroInclude(MacroNode $node, $isDefinition = FALSE)
	{
		$destination = $this->writer->fetchWord($node->args); // destination [,] [params]
		$params = $this->writer->formatArray($node->args) . ($node->args ? ' + ' : '');

		if ($destination === NULL) {
			throw new ParseException("Missing destination in {include}", 0, $this->parser->line);

		} elseif ($destination[0] === '#') { // include #block
			$destination = ltrim($destination, '#');
			if (!Strings::match($destination, '#^\$?' . self::RE_IDENTIFIER . '$#')) {
				throw new ParseException("Included block name must be alphanumeric string, '$destination' given.", 0, $this->parser->line);
			}

			$parent = $destination === 'parent';
			if ($destination === 'parent' || $destination === 'this') {
				$item = $node->parentNode;
				while ($item && $item->name !== 'block' && !isset($item->data->name)) $item = $item->parentNode;
				if (!$item) {
					throw new ParseException("Cannot include $destination block outside of any block.", 0, $this->parser->line);
				}
				$destination = $item->data->name;
			}
			$name = $destination[0] === '$' ? $destination : var_export($destination, TRUE);
			$params .= $isDefinition ? 'get_defined_vars()' : '$template->getParams()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_l->blocks[$name]), \$_l, $params)"
				: 'Nette\Latte\DefaultMacros::callBlock' . ($parent ? 'Parent' : '') . "(\$_l, $name, $params)";
			return $node->modifiers
				? "ob_start(); $cmd; echo " . $this->writer->formatModifiers('ob_get_clean()', $node->modifiers, $this->parser->escape)
				: $cmd;

		} else { // include "file"
			$destination = $this->writer->formatWord($destination);
			$cmd = 'Nette\Latte\DefaultMacros::includeTemplate(' . $destination . ', '
				. $params . '$template->getParams(), $_l->templates[' . var_export($this->parser->templateId, TRUE) . '])';
			return $node->modifiers
				? 'echo ' . $this->writer->formatModifiers($cmd . '->__toString(TRUE)', $node->modifiers, $this->parser->escape)
				: $cmd . '->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	public function macroExtends(MacroNode $node)
	{
		if (!$node->args) {
			throw new ParseException("Missing destination in {extends}", 0, $this->parser->line);
		}
		if (!empty($node->parentNode)) {
			throw new ParseException("{extends} must be placed outside any macro.", 0, $this->parser->line);
		}
		if ($this->extends !== NULL) {
			throw new ParseException("Multiple {extends} declarations are not allowed.", 0, $this->parser->line);
		}
		$this->extends = $node->args !== 'none';
		return $this->extends ? '$_l->extends = ' . ($node->args === 'auto' ? '$layout' : $this->writer->formatArgs($node->args)) : '';
	}



	/**
	 * {block ...} {snippet ...}
	 */
	public function macroBlock(MacroNode $node)
	{
		$name = $this->writer->fetchWord($node->args); // block [,] [params]

		if ($node->name === 'block' && $name === NULL) { // anonymous block
			return $node->modifiers === '' ? '' : 'ob_start()';

		} else { // #block
			$name = ($node->name === 'snippet' ? '_' : '') . ltrim($name, '#');
			if (!Strings::match($name, '#^' . self::RE_IDENTIFIER . '$#')) {
				throw new ParseException("Block name must be alphanumeric string, '$name' given.", 0, $this->parser->line);

			} elseif (isset($this->namedBlocks[$name])) {
				throw new ParseException("Cannot redeclare block '$name'", 0, $this->parser->line);
			}

			$top = empty($node->parentNode);
			$this->namedBlocks[$name] = TRUE;
			$node->data->name = $name;
			if ($node->name === 'snippet') {
				$tag = $this->writer->fetchWord($node->args);  // [name [,]] [tag]
				$tag = trim($tag, '<>');
				$namePhp = var_export(substr($name, 1), TRUE);
				$tag = $tag ? $tag : 'div';
				$node->args = '#' . $name;
				return "?><$tag id=\"<?php echo \$control->getSnippetId($namePhp) ?>\"><?php "
					. $this->macroInclude($node)
					. " ?></$tag><?php ";

			} elseif (!$top) {
				$node->args = '#' . $name;
				return $this->macroInclude($node, TRUE);

			} elseif ($this->extends) {
				return '';

			} else {
				$node->args = '#' . $name;
				return 'if (!$_l->extends) { ' . $this->macroInclude($node, TRUE) . '; }';
			}
		}
	}



	/**
	 * {/block} {/snippet}
	 */
	public function macroBlockEnd(MacroNode $node)
	{
		if ($node->name === 'capture') { // capture - back compatibility
			return $this->macroCaptureEnd($node);

		} elseif (($node->name === 'block' && isset($node->data->name)) || $node->name === 'snippet') { // block
			$this->namedBlocks[$node->data->name] = $node->content;
			return $node->content = '';

		} else { // anonymous block
			return $node->modifiers === '' ? '' : 'echo ' . $this->writer->formatModifiers('ob_get_clean()', $node->modifiers, $this->parser->escape);
		}
	}



	/**
	 * {capture ...}
	 */
	public function macroCapture(MacroNode $node)
	{
		$name = $this->writer->fetchWord($node->args); // $variable

		if (substr($name, 0, 1) !== '$') {
			throw new ParseException("Invalid capture block parameter '$name'", 0, $this->parser->line);
		}
		$node->data->name = $name;
		return 'ob_start()';
	}



	/**
	 * {/capture}
	 */
	public function macroCaptureEnd(MacroNode $node)
	{
		return $node->data->name . '=' . $this->writer->formatModifiers('ob_get_clean()', $node->modifiers, $this->parser->escape);
	}



	/**
	 * {cache ...}
	 */
	public function macroCache(MacroNode $node)
	{
		return 'if (Nette\Latte\DefaultMacros::createCache($netteCacheStorage, '
			. var_export($this->parser->templateId . ':' . $this->cacheCounter++, TRUE)
			. ', $_g->caches' . $this->writer->formatArray($node->args, ', ') . ')) {';
	}



	/**
	 * {foreach ...}
	 */
	public function macroForeach(MacroNode $node)
	{
		return '$iterator = $_l->its[] = new Nette\Iterators\CachingIterator('
			. preg_replace('#(.*)\s+as\s+#i', '$1) as ', $this->writer->formatArgs($node->args), 1);
	}



	/**
	 * {ifset ...}
	 */
	public function macroIfset(MacroNode $node)
	{
		if (strpos($node->args, '#') === FALSE) {
			return $node->args;
		}
		$list = array();
		while (($name = $this->writer->fetchWord($node->args)) !== NULL) {
			$list[] = $name[0] === '#' ? '$_l->blocks["' . substr($name, 1) . '"]' : $name;
		}
		return implode(', ', $list);
	}



	/**
	 * {attr ...}
	 */
	public function macroAttr(MacroNode $node)
	{
		return Strings::replace($node->args . ' ', '#\)\s+#', ')->');
	}



	/**
	 * {contentType ...}
	 */
	public function macroContentType(MacroNode $node)
	{
		if (strpos($node->args, 'html') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeHtml|';
			$this->parser->context = Parser::CONTEXT_TEXT;

		} elseif (strpos($node->args, 'xml') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeXml';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($node->args, 'javascript') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeJs';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($node->args, 'css') !== FALSE) {
			$this->parser->escape = 'Nette\Templating\DefaultHelpers::escapeCss';
			$this->parser->context = Parser::CONTEXT_NONE;

		} elseif (strpos($node->args, 'plain') !== FALSE) {
			$this->parser->escape = '';
			$this->parser->context = Parser::CONTEXT_NONE;

		} else {
			$this->parser->escape = '$template->escape';
			$this->parser->context = Parser::CONTEXT_NONE;
		}

		// temporary solution
		if (strpos($node->args, '/')) {
			return '$netteHttpResponse->setHeader("Content-Type", "' . $node->args . '")';
		}
	}



	/**
	 * {dump ...}
	 */
	public function macroDump(MacroNode $node)
	{
		return 'Nette\Diagnostics\Debugger::barDump('
			. ($node->args ? 'array(' . var_export($this->writer->formatArgs($node->args), TRUE) . " => $node->args)" : 'get_defined_vars()')
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
	public function macroControl(MacroNode $node)
	{
		$pair = $this->writer->fetchWord($node->args); // control[:method]
		if ($pair === NULL) {
			throw new ParseException("Missing control name in {control}", 0, $this->parser->line);
		}
		$pair = explode(':', $pair, 2);
		$name = $this->writer->formatWord($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = Strings::match($method, '#^(' . self::RE_IDENTIFIER . '|)$#') ? "render$method" : "{\"render$method\"}";
		$param = $this->writer->formatArray($node->args);
		if (strpos($node->args, '=>') === FALSE) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_ctrl = $control->getWidget(' . $name . '); '
			. 'if ($_ctrl instanceof Nette\Application\UI\IPartiallyRenderable) $_ctrl->validateControl(); '
			. "\$_ctrl->$method($param)";
	}



	/**
	 * {link ...} {plink ...}
	 */
	public function macroLink(MacroNode $node)
	{
		return $this->writer->formatModifiers(($node->name === 'plink' ? '$presenter' : '$control') . '->link(' . $this->formatLink($node) .')', $node->modifiers, $this->parser->escape);
	}



	/**
	 * {ifCurrent ...}
	 */
	public function macroIfCurrent(MacroNode $node)
	{
		return ($node->args ? 'try { $presenter->link(' . $this->formatLink($node) . '); } catch (Nette\Application\UI\InvalidLinkException $e) {}' : '')
			. '; if ($presenter->getLastCreatedRequestFlag("current")):';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink(MacroNode $node)
	{
		return $this->writer->formatWord($this->writer->fetchWord($node->args)) . $this->writer->formatArray($node->args, ', '); // destination [,] args
	}



	/**
	 * {var ...} {default ...}
	 */
	public function macroVar(MacroNode $node)
	{
		$out = '';
		$var = TRUE;
		$tokenizer = $this->writer->preprocess(new MacroTokenizer($node->args));
		while ($token = $tokenizer->fetchToken()) {
			if ($var && ($token['type'] === MacroTokenizer::T_SYMBOL || $token['type'] === MacroTokenizer::T_VARIABLE)) {
				if ($node->name === 'default') {
					$out .= "'" . trim($token['value'], "'$") . "'";
				} else {
					$out .= '$' . trim($token['value'], "'$");
				}
			} elseif (($token['value'] === '=' || $token['value'] === '=>') && $token['depth'] === 0) {
				$out .= $node->name === 'default' ? '=>' : '=';
				$var = FALSE;

			} elseif ($token['value'] === ',' && $token['depth'] === 0) {
				$out .= $node->name === 'default' ? ',' : ';';
				$var = TRUE;
			} else {
				$out .= $this->writer->canQuote($tokenizer) ? "'$token[value]'" : $token['value'];
			}
		}
		return $node->name === 'default' ? "extract(array($out), EXTR_SKIP)" : $out;
	}



	/**
	 * Just modifiers helper.
	 */
	public function macroModifiers(MacroNode $node)
	{
		return $this->writer->formatModifiers($this->writer->formatArgs($node->args), $node->modifiers, $this->parser->escape);
	}



	/**
	 * Argument formating helper.
	 */
	public function formatArray(MacroNode $node)
	{
		return $this->writer->formatArray($node->args);
	}



	/**
	 * Escaping helper.
	 */
	public function escape()
	{
		$tmp = explode('|', $this->parser->escape);
		return $tmp[0];
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
	public static function initRuntime($template, $extends, $templateId)
	{
		// local storage
		if (isset($template->_l)) {
			$local = $template->_l;
			unset($template->_l);
		} else {
		$local = (object) NULL;
		}
		$local->templates[$templateId] = $template;

		// global storage
		if (!isset($template->_g)) {
			$template->_g = (object) NULL;
		}
		$global = $template->_g;

		// extends support
		$local->extends = is_bool($extends) ? $extends : (empty($template->_extends) ? FALSE : $template->_extends);
		unset($template->_extends);

		// cache support
		if (!empty($global->caches)) {
			end($global->caches)->dependencies[Nette\Caching\Cache::FILES][] = $template->getFile();
		}

		return array($local, $global);
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
