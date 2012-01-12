<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte\Macros;

use Nette,
	Nette\Latte,
	Nette\Latte\MacroNode,
	Nette\Latte\ParseException,
	Nette\Utils\Strings;



/**
 * Macros for Nette\Application\UI.
 *
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {contentType ...} HTTP Content-Type header
 * - {status ...} HTTP status
 *
 * @author     David Grudl
 */
class UIMacros extends MacroSet
{
	/** @internal PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;



	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('include', array($me, 'macroInclude'));
		$me->addMacro('includeblock', array($me, 'macroIncludeBlock'));
		$me->addMacro('extends', array($me, 'macroExtends'));
		$me->addMacro('layout', array($me, 'macroExtends'));
		$me->addMacro('block', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('define', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('snippet', array($me, 'macroBlock'), array($me, 'macroBlockEnd'));
		$me->addMacro('ifset', array($me, 'macroIfset'), 'endif');

		$me->addMacro('widget', array($me, 'macroControl')); // deprecated - use control
		$me->addMacro('control', array($me, 'macroControl'));

		$me->addMacro('@href', function(MacroNode $node, $writer) use ($me) {
			return ' ?> href="<?php ' . $me->macroLink($node, $writer) . ' ?>"<?php ';
		});
		$me->addMacro('plink', array($me, 'macroLink'));
		$me->addMacro('link', array($me, 'macroLink'));
		$me->addMacro('ifCurrent', array($me, 'macroIfCurrent'), 'endif'); // deprecated; use n:class="$presenter->linkCurrent ? ..."

		$me->addMacro('contentType', array($me, 'macroContentType'));
		$me->addMacro('status', array($me, 'macroStatus'));
	}



	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->namedBlocks = array();
		$this->extends = NULL;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		// try close last block
		try {
			$this->getCompiler()->writeMacro('/block');
		} catch (ParseException $e) {
		}

		$epilog = $prolog = array();

		if ($this->namedBlocks) {
			foreach ($this->namedBlocks as $name => $code) {
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				$snippet = $name[0] === '_';
				$prolog[] = "//\n// block $name\n//\n"
					. "if (!function_exists(\$_l->blocks[" . var_export($name, TRUE) . "][] = '$func')) { "
					. "function $func(\$_l, \$_args) { "
					. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v') // PHP bug #46873
					. ($snippet ? '; $_control->validateControl(' . var_export(substr($name, 1), TRUE) . ')' : '')
					. "\n?>$code<?php\n}}";
			}
			$prolog[] = "//\n// end of blocks\n//";
		}

		if ($this->namedBlocks || $this->extends) {
			$prolog[] = "// template extending and snippets support";

			if (is_bool($this->extends)) {
				$prolog[] = '$_l->extends = ' . var_export($this->extends, TRUE) . '; unset($_extends, $template->_extends);';
			} else {
				$prolog[] = '$_l->extends = empty($template->_extends) ? FALSE : $template->_extends; unset($_extends, $template->_extends);';
			}

			$prolog[] = '
if ($_l->extends) {
	ob_start();
} elseif (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';
			$epilog[] = '
// template extending support
if ($_l->extends) {
	ob_end_clean();
	Nette\Latte\Macros\CoreMacros::includeTemplate($_l->extends, get_defined_vars(), $template)->render();
}';
		} else {
			$prolog[] = '
// snippets support
if (!empty($_control->snippetMode)) {
	return Nette\Latte\Macros\UIMacros::renderSnippets($_control, $_l, get_defined_vars());
}';
		}

		return array(implode("\n\n", $prolog), implode("\n", $epilog));
	}



	/********************* macros ****************d*g**/



	/**
	 * {include #block}
	 */
	public function macroInclude(MacroNode $node, $writer)
	{
		$destination = $node->tokenizer->fetchWord(); // destination [,] [params]
		if (substr($destination, 0, 1) !== '#') {
			return FALSE;
		}

		$destination = ltrim($destination, '#');
		if (!Strings::match($destination, '#^\$?' . self::RE_IDENTIFIER . '$#')) {
			throw new ParseException("Included block name must be alphanumeric string, '$destination' given.");
		}

		$parent = $destination === 'parent';
		if ($destination === 'parent' || $destination === 'this') {
			$item = $node->parentNode;
			while ($item && $item->name !== 'block' && !isset($item->data->name)) $item = $item->parentNode;
			if (!$item) {
				throw new ParseException("Cannot include $destination block outside of any block.");
			}
			$destination = $item->data->name;
		}

		$name = $destination[0] === '$' ? $destination : var_export($destination, TRUE);
		if (isset($this->namedBlocks[$destination]) && !$parent) {
			$cmd = "call_user_func(reset(\$_l->blocks[$name]), \$_l, %node.array? + \$template->getParameters())";
		} else {
			$cmd = 'Nette\Latte\Macros\UIMacros::callBlock' . ($parent ? 'Parent' : '') . "(\$_l, $name, %node.array? + \$template->getParameters())";
		}

		if ($node->modifiers) {
			return $writer->write("ob_start(); $cmd; echo %modify(ob_get_clean())");
		} else {
			return $writer->write($cmd);
		}
	}



	/**
	 * {includeblock "file"}
	 */
	public function macroIncludeBlock(MacroNode $node, $writer)
	{
		return $writer->write('Nette\Latte\Macros\CoreMacros::includeTemplate(%node.word, %node.array? + get_defined_vars(), $_l->templates[%var])->render()',
			$this->getCompiler()->getTemplateId());
	}



	/**
	 * {extends auto | none | $var | "file"}
	 */
	public function macroExtends(MacroNode $node, $writer)
	{
		if (!$node->args) {
			throw new ParseException("Missing destination in {extends}");
		}
		if (!empty($node->parentNode)) {
			throw new ParseException("{extends} must be placed outside any macro.");
		}
		if ($this->extends !== NULL) {
			throw new ParseException("Multiple {extends} declarations are not allowed.");
		}
		$this->extends = $node->args !== 'none';
		return $this->extends ? '$_l->extends = ' . ($node->args === 'auto' ? '$layout' : $writer->formatArgs()) : '';
	}



	/**
	 * {block [[#]name]}
	 * {snippet [name [,]] [tag]}
	 * {define [#]name}
	 */
	public function macroBlock(MacroNode $node, $writer)
	{
		$name = $node->tokenizer->fetchWord();

		if ($node->name === 'block' && $name === FALSE) { // anonymous block
			return $node->modifiers === '' ? '' : 'ob_start()';
		}

		$node->data->name = $name = ltrim($name, '#');
		$node->data->end = '';
		if ($name == NULL) {
			if ($node->name !== 'snippet') {
				throw new ParseException("Missing block name.");
			}

		} elseif (!Strings::match($name, '#^' . self::RE_IDENTIFIER . '$#')) { // dynamic blok/snippet
			if ($node->name === 'snippet') {
				$parent = $node->parentNode;
				while ($parent && $parent->name !== 'snippet') $parent = $parent->parentNode;
				if (!$parent) {
					throw new ParseException("Dynamic snippets are allowed only inside static snippet.");
				}
				$parent->data->dynamic = TRUE;

				$tag = trim($node->tokenizer->fetchWord(), '<>');
				$tag = $tag ? $tag : 'div';
				$node->data->leave = TRUE;
				$node->data->end = "\$_dynSnippets[\$_dynSnippetId] = ob_get_flush() ?>\n</$tag><?php";
				return $writer->write("?>\n<$tag id=\"<?php echo \$_dynSnippetId = \$_control->getSnippetId({$writer->formatWord($name)}) ?>\"><?php ob_start()");

			} else {
				$node->data->leave = TRUE;
				$fname = $writer->formatWord($name);
				$node->data->end = "}} call_user_func(reset(\$_l->blocks[$fname]), \$_l, get_defined_vars())";
				$func = '_lb' . substr(md5($this->getCompiler()->getTemplateId() . $name), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
				return "//\n// block $name\n//\n"
					. "if (!function_exists(\$_l->blocks[$fname][] = '$func')) { "
					. "function $func(\$_l, \$_args) { "
					. (PHP_VERSION_ID > 50208 ? 'extract($_args)' : 'foreach ($_args as $__k => $__v) $$__k = $__v'); // PHP bug #46873
			}
		}

		// static blok/snippet
		if ($node->name === 'snippet') {
			$node->data->name = $name = '_' . $name;
		}
		if (isset($this->namedBlocks[$name])) {
			throw new ParseException("Cannot redeclare static block '$name'");
		}
		$top = empty($node->parentNode);
		$this->namedBlocks[$name] = TRUE;

		$include = 'call_user_func(reset($_l->blocks[%var]), $_l, ' . ($node->name === 'snippet' ? '$template->getParameters()' : 'get_defined_vars()') . ')';
		if ($node->modifiers) {
			$include = "ob_start(); $include; echo %modify(ob_get_clean())";
		}

		if ($node->name === 'snippet') {
			$tag = trim($node->tokenizer->fetchWord(), '<>');
			$tag = $tag ? $tag : 'div';
			return $writer->write("?>\n<$tag id=\"<?php echo \$_control->getSnippetId(%var) ?>\"><?php $include ?>\n</$tag><?php ",
				(string) substr($name, 1), $name
			);

		} elseif ($node->name === 'define') {
			return '';

		} elseif (!$top) {
			return $writer->write($include, $name);

		} elseif ($this->extends) {
			return '';

		} else {
			return $writer->write("if (!\$_l->extends) { $include; }", $name);
		}
	}



	/**
	 * {/block}
	 * {/snippet}
	 * {/define}
	 */
	public function macroBlockEnd(MacroNode $node, $writer)
	{
		if (isset($node->data->name)) { // block, snippet, define
			if (empty($node->data->leave)) {
				if (!empty($node->data->dynamic)) {
					$node->content .= '<?php if (isset($_dynSnippets)) return $_dynSnippets; ?>';
				}
				$this->namedBlocks[$node->data->name] = $node->content;
				$node->content = '';
			}
			return $node->data->end;

		} elseif ($node->modifiers) { // anonymous block with modifier
			return $writer->write('echo %modify(ob_get_clean())');
		}
	}



	/**
	 * {ifset #block}
	 */
	public function macroIfset(MacroNode $node, $writer)
	{
		if (!Strings::contains($node->args, '#')) {
			return FALSE;
		}
		$list = array();
		while (($name = $node->tokenizer->fetchWord()) !== FALSE) {
			$list[] = $name[0] === '#' ? '$_l->blocks["' . substr($name, 1) . '"]' : $name;
		}
		return 'if (isset(' . implode(', ', $list) . ')):';
	}



	/**
	 * {control name[:method] [params]}
	 */
	public function macroControl(MacroNode $node, $writer)
	{
		$pair = $node->tokenizer->fetchWord();
		if ($pair === FALSE) {
			throw new ParseException("Missing control name in {control}");
		}
		$pair = explode(':', $pair, 2);
		$name = $writer->formatWord($pair[0]);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = Strings::match($method, '#^(' . self::RE_IDENTIFIER . '|)$#') ? "render$method" : "{\"render$method\"}";
		$param = $writer->formatArray();
		if (!Strings::contains($node->args, '=>')) {
			$param = substr($param, 6, -1); // removes array()
		}
		return ($name[0] === '$' ? "if (is_object($name)) \$_ctrl = $name; else " : '')
			. '$_ctrl = $_control->getComponent(' . $name . '); '
			. 'if ($_ctrl instanceof Nette\Application\UI\IRenderable) $_ctrl->validateControl(); '
			. "\$_ctrl->$method($param)";
	}



	/**
	 * {link destination [,] [params]}
	 * {plink destination [,] [params]}
	 * n:href="destination [,] [params]"
	 */
	public function macroLink(MacroNode $node, $writer)
	{
		return $writer->write('echo %escape(' . ($node->name === 'plink' ? '$_presenter' : '$_control') . '->link(%node.word, %node.array?))');
	}



	/**
	 * {ifCurrent destination [,] [params]}
	 */
	public function macroIfCurrent(MacroNode $node, $writer)
	{
		return $writer->write(($node->args ? 'try { $_presenter->link(%node.word, %node.array?); } catch (Nette\Application\UI\InvalidLinkException $e) {}' : '')
			. '; if ($_presenter->getLastCreatedRequestFlag("current")):');
	}



	/**
	 * {contentType ...}
	 */
	public function macroContentType(MacroNode $node, $writer)
	{
		if (Strings::contains($node->args, 'html')) {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_HTML);

		} elseif (Strings::contains($node->args, 'xml')) {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_XML);

		} elseif (Strings::contains($node->args, 'javascript')) {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_JS);

		} elseif (Strings::contains($node->args, 'css')) {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_CSS);

		} elseif (Strings::contains($node->args, 'calendar')) {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_ICAL);

		} else {
			$this->getCompiler()->setContext(Latte\Compiler::CONTEXT_NONE);
		}

		// temporary solution
		if (Strings::contains($node->args, '/')) {
			return $writer->write('$netteHttpResponse->setHeader("Content-Type", %var)', $node->args);
		}
	}



	/**
	 * {status ...}
	 */
	public function macroStatus(MacroNode $node, $writer)
	{
		return $writer->write((substr($node->args, -1) === '?' ? 'if (!$netteHttpResponse->isSent()) ' : '') .
			'$netteHttpResponse->setCode(%var)', (int) $node->args
		);
	}



	/********************* run-time writers ****************d*g**/



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



	public static function renderSnippets($control, $local, $params)
	{
		$control->snippetMode = FALSE;
		$payload = $control->getPresenter()->getPayload();
		if (isset($local->blocks)) {
			foreach ($local->blocks as $name => $function) {
				if ($name[0] !== '_' || !$control->isControlInvalid(substr($name, 1))) {
					continue;
				}
				ob_start();
				$function = reset($function);
				$snippets = $function($local, $params);
				$payload->snippets[$id = $control->getSnippetId(substr($name, 1))] = ob_get_clean();
				if ($snippets) {
					$payload->snippets += $snippets;
					unset($payload->snippets[$id]);
			}
		}
		}
		if ($control instanceof Nette\Application\UI\Control) {
			foreach ($control->getComponents(FALSE, 'Nette\Application\UI\Control') as $child) {
				if ($child->isControlInvalid()) {
					$child->snippetMode = TRUE;
					$child->render();
					$child->snippetMode = FALSE;
				}
			}
		}
	}

}
