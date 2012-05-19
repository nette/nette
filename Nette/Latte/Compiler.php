<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Latte;

use Nette,
	Nette\Utils\Strings;



/**
 * Latte compiler.
 *
 * @author     David Grudl
 */
class Compiler extends Nette\Object
{
	/** @var string default content type */
	public $defaultContentType = self::CONTENT_XHTML;

	/** @var Token[] */
	private $tokens;

	/** @var string pointer to current node content */
	private $output;

	/** @var int  position on source template */
	private $position;

	/** @var array of [name => IMacro[]] */
	private $macros;

	/** @var \SplObjectStorage */
	private $macroHandlers;

	/** @var HtmlNode */
	private $htmlNode;

	/** @var MacroNode */
	private $macroNode;

	/** @var array of string */
	private $attrCodes = array();

	/** @var string */
	private $contentType;

	/** @var array */
	private $context;

	/** @var string */
	private $templateId;

	/** Context-aware escaping states */
	const CONTENT_HTML = 'html',
		CONTENT_XHTML = 'xhtml',
		CONTENT_XML = 'xml',
		CONTENT_JS = 'js',
		CONTENT_CSS = 'css',
		CONTENT_ICAL = 'ical',
		CONTENT_TEXT = 'text';

	/** @internal Context-aware escaping states */
	const CONTEXT_COMMENT = 'comment',
		CONTEXT_SINGLE_QUOTED = "'",
		CONTEXT_DOUBLE_QUOTED = '"',
		CONTEXT_UNQUOTED = '=';


	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
	}



	/**
	 * Adds new macro.
	 * @param  string
	 * @return Compiler  provides a fluent interface
	 */
	public function addMacro($name, IMacro $macro)
	{
		$this->macros[$name][] = $macro;
		$this->macroHandlers->attach($macro);
		return $this;
	}



	/**
	 * Compiles tokens to PHP code.
	 * @param  array
	 * @return string
	 */
	public function compile(array $tokens)
	{
		$this->templateId = Strings::random();
		$this->tokens = $tokens;
		$output = '';
		$this->output = & $output;
		$this->htmlNode = $this->macroNode = NULL;
		$this->setContentType($this->defaultContentType);

		foreach ($this->macroHandlers as $handler) {
			$handler->initialize($this);
		}

		try {
			foreach ($tokens as $this->position => $token) {
				$this->{"process$token->type"}($token);
			}
		} catch (CompileException $e) {
			$e->sourceLine = $token->line;
			throw $e;
		}

		while ($this->htmlNode) {
			if (!empty($this->htmlNode->macroAttrs)) {
				throw new CompileException("Missing end tag </{$this->htmlNode->name}> for macro-attribute " . Parser::N_PREFIX
					. implode(' and ' . Parser::N_PREFIX, array_keys($this->htmlNode->macroAttrs)) . ".", 0, $token->line);
			}
			$this->htmlNode = $this->htmlNode->parentNode;
		}

		$prologs = $epilogs = '';
		foreach ($this->macroHandlers as $handler) {
			$res = $handler->finalize();
			$handlerName = get_class($handler);
			$prologs .= empty($res[0]) ? '' : "<?php\n// prolog $handlerName\n$res[0]\n?>";
			$epilogs = (empty($res[1]) ? '' : "<?php\n// epilog $handlerName\n$res[1]\n?>") . $epilogs;
		}
		$output = ($prologs ? $prologs . "<?php\n//\n// main template\n//\n?>\n" : '') . $output . $epilogs;

		if ($this->macroNode) {
			throw new CompileException("There are unclosed macros.", 0, $token->line);
		}

		$output = $this->expandTokens($output);
		return $output;
	}



	/**
	 * @return Compiler  provides a fluent interface
	 */
	public function setContentType($type)
	{
		$this->contentType = $type;
		$this->context = NULL;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}



	/**
	 * @return Compiler  provides a fluent interface
	 */
	public function setContext($context, $sub = NULL)
	{
		$this->context = array($context, $sub);
		return $this;
	}



	/**
	 * @return array [context, spec]
	 */
	public function getContext()
	{
		return $this->context;
	}



	/**
	 * @return string
	 */
	public function getTemplateId()
	{
		return $this->templateId;
	}



	/**
	 * @return MacroNode|NULL
	 */
	public function getMacroNode()
	{
		return $this->macroNode;
	}



	/**
	 * Returns current line number.
	 * @return int
	 */
	public function getLine()
	{
		return $this->tokens ? $this->tokens[$this->position]->line : NULL;
	}



	public function expandTokens($s)
	{
		return strtr($s, $this->attrCodes);
	}



	private function processText(Token $token)
	{
		if (($this->context[0] === self::CONTEXT_SINGLE_QUOTED || $this->context[0] === self::CONTEXT_DOUBLE_QUOTED)
			&& $token->text === $this->context[0])
		{
			$this->setContext(self::CONTEXT_UNQUOTED);
		}
		$this->output .= $token->text;
	}



	private function processMacroTag(Token $token)
	{
		$isRightmost = !isset($this->tokens[$this->position + 1])
			|| substr($this->tokens[$this->position + 1]->text, 0, 1) === "\n";
		$this->writeMacro($token->name, $token->value, $token->modifiers, $isRightmost);
	}



	private function processHtmlTagBegin(Token $token)
	{
		if ($token->closing) {
			while ($this->htmlNode) {
				if (strcasecmp($this->htmlNode->name, $token->name) === 0) {
					break;
				}
				if ($this->htmlNode->macroAttrs) {
					throw new CompileException("Unexpected </$token->name>.", 0, $token->line);
				}
				$this->htmlNode = $this->htmlNode->parentNode;
			}
			if (!$this->htmlNode) {
				$this->htmlNode = new HtmlNode($token->name);
			}
			$this->htmlNode->closing = TRUE;
			$this->htmlNode->offset = strlen($this->output);
			$this->setContext(NULL);

		} elseif ($token->text === '<!--') {
			$this->setContext(self::CONTEXT_COMMENT);

		} else {
			$this->htmlNode = new HtmlNode($token->name, $this->htmlNode);
			$this->htmlNode->isEmpty = in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))
				&& isset(Nette\Utils\Html::$emptyElements[strtolower($token->name)]);
			$this->htmlNode->offset = strlen($this->output);
			$this->setContext(self::CONTEXT_UNQUOTED);
		}
		$this->output .= $token->text;
	}



	private function processHtmlTagEnd(Token $token)
	{
		if ($token->text === '-->') {
			$this->output .= $token->text;
			$this->setContext(NULL);
			return;
		}

		$htmlNode = $this->htmlNode;
		$isEmpty = !$htmlNode->closing && (Strings::contains($token->text, '/') || $htmlNode->isEmpty);

		if ($isEmpty && in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))) { // auto-correct
			$token->text = preg_replace('#^.*>#', $this->contentType === self::CONTENT_XHTML ? ' />' : '>', $token->text);
		}

		if (empty($htmlNode->macroAttrs)) {
			$this->output .= $token->text;
		} else {
			$code = substr($this->output, $htmlNode->offset) . $token->text;
			$this->output = substr($this->output, 0, $htmlNode->offset);
			$this->writeAttrsMacro($code);
			if ($isEmpty) {
				$htmlNode->closing = TRUE;
				$this->writeAttrsMacro('');
			}
		}

		if ($isEmpty) {
			$htmlNode->closing = TRUE;
		}

		if (!$htmlNode->closing && (strcasecmp($htmlNode->name, 'script') === 0 || strcasecmp($htmlNode->name, 'style') === 0)) {
			$this->setContext(strcasecmp($htmlNode->name, 'style') ? self::CONTENT_JS : self::CONTENT_CSS);
		} else {
			$this->setContext(NULL);
			if ($htmlNode->closing) {
				$this->htmlNode = $this->htmlNode->parentNode;
			}
		}
	}



	private function processHtmlAttribute(Token $token)
	{
		if (Strings::startsWith($token->name, Parser::N_PREFIX)) {
			$this->htmlNode->macroAttrs[substr($token->name, strlen(Parser::N_PREFIX))] = $token->value;
		} else {
			$this->htmlNode->attrs[$token->name] = TRUE;
			$this->output .= $token->text;
			if ($token->value) { // quoted
				$context = NULL;
				if (strncasecmp($token->name, 'on', 2) === 0) {
					$context = self::CONTENT_JS;
				} elseif ($token->name === 'style') {
					$context = self::CONTENT_CSS;
				}
				$this->setContext($token->value, $context);
			}
		}
	}



	private function processComment(Token $token)
	{
		$isLeftmost = trim(substr($this->output, strrpos("\n$this->output", "\n"))) === '';
		if (!$isLeftmost) {
			$this->output .= substr($token->text, strlen(rtrim($token->text, "\n")));
		}
	}



	/********************* macros ****************d*g**/



	/**
	 * Generates code for {macro ...} to the output.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return MacroNode
	 */
	public function writeMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE, $nPrefix = NULL)
	{
		if ($name[0] === '/') { // closing
			$node = $this->macroNode;

			if (!$node || ("/$node->name" !== $name && '/' !== $name) || $modifiers
				|| ($args && $node->args && !Strings::startsWith("$node->args ", "$args "))
			) {
				$name .= $args ? ' ' : '';
				throw new CompileException("Unexpected macro {{$name}{$args}{$modifiers}}"
					. ($node ? ", expecting {/$node->name}" . ($args && $node->args ? " or eventually {/$node->name $node->args}" : '') : ''));
			}

			$this->macroNode = $node->parentNode;
			if (!$node->args) {
				$node->setArgs($args);
			}

			$isLeftmost = $node->content ? trim(substr($this->output, strrpos("\n$this->output", "\n"))) === '' : FALSE;

			$node->closing = TRUE;
			$node->macro->nodeClosed($node);

			$this->output = & $node->saved[0];
			$this->writeCode($node->openingCode, $this->output, $node->saved[1]);
			$this->writeCode($node->closingCode, $node->content, $isRightmost, $isLeftmost);
			$this->output .= $node->content;

		} else { // opening
			$node = $this->expandMacro($name, $args, $modifiers, $nPrefix);
			if ($node->isEmpty) {
				$this->writeCode($node->openingCode, $this->output, $isRightmost);

			} else {
				$this->macroNode = $node;
				$node->saved = array(& $this->output, $isRightmost);
				$this->output = & $node->content;
			}
		}
		return $node;
	}



	private function writeCode($code, & $output, $isRightmost, $isLeftmost = NULL)
	{
		if ($isRightmost) {
			$leftOfs = strrpos("\n$output", "\n");
			$isLeftmost = $isLeftmost === NULL ? trim(substr($output, $leftOfs)) === '' : $isLeftmost;
			if ($isLeftmost && substr($code, 0, 11) !== '<?php echo ') {
				$output = substr($output, 0, $leftOfs); // alone macro without output -> remove indentation
			} elseif (substr($code, -2) === '?>') {
				$code .= "\n"; // double newline to avoid newline eating by PHP
			}
		}
		$output .= $code;
	}



	/**
	 * Generates code for macro <tag n:attr> to the output.
	 * @param  string
	 * @return void
	 */
	public function writeAttrsMacro($code)
	{
		$attrs = $this->htmlNode->macroAttrs;
		$left = $right = array();
		$attrCode = '';

		foreach ($this->macros as $name => $foo) {
			$attrName = MacroNode::PREFIX_INNER . "-$name";
			if (isset($attrs[$attrName])) {
				if ($this->htmlNode->closing) {
					$left[] = array("/$name", '', MacroNode::PREFIX_INNER);
				} else {
					array_unshift($right, array($name, $attrs[$attrName], MacroNode::PREFIX_INNER));
				}
				unset($attrs[$attrName]);
			}
		}

		foreach (array_reverse($this->macros) as $name => $foo) {
			$attrName = MacroNode::PREFIX_TAG . "-$name";
			if (isset($attrs[$attrName])) {
				$left[] = array($name, $attrs[$attrName], MacroNode::PREFIX_TAG);
				array_unshift($right, array("/$name", '', MacroNode::PREFIX_TAG));
				unset($attrs[$attrName]);
			}
		}

		foreach ($this->macros as $name => $foo) {
			if (isset($attrs[$name])) {
				if ($this->htmlNode->closing) {
					$right[] = array("/$name", '', MacroNode::PREFIX_NONE);
				} else {
					array_unshift($left, array($name, $attrs[$name], MacroNode::PREFIX_NONE));
				}
				unset($attrs[$name]);
			}
		}

		if ($attrs) {
			throw new CompileException("Unknown macro-attribute " . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($attrs)));
		}

		if (!$this->htmlNode->closing) {
			$this->htmlNode->attrCode = & $this->attrCodes[$uniq = ' n:' . Nette\Utils\Strings::random()];
			$code = substr_replace($code, $uniq, strrpos($code, '/>') ?: strrpos($code, '>'), 0);
		}

		foreach ($left as $item) {
			$node = $this->writeMacro($item[0], $item[1], NULL, NULL, $item[2]);
			if ($node->closing || $node->isEmpty) {
				$this->htmlNode->attrCode .= $node->attrCode;
				if ($node->isEmpty) {
					unset($this->htmlNode->macroAttrs[$node->name]);
				}
			}
		}

		$this->output .= $code;

		foreach ($right as $item) {
			$node = $this->writeMacro($item[0], $item[1], NULL, NULL, MacroNode::PREFIX_NONE);
			if ($node->closing) {
				$this->htmlNode->attrCode .= $node->attrCode;
			}
		}

		if ($right && substr($this->output, -2) === '?>') {
			$this->output .= "\n";
		}
	}



	/**
	 * Expands macro and returns node & code.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MacroNode
	 */
	public function expandMacro($name, $args, $modifiers = NULL, $nPrefix = NULL)
	{
		if (empty($this->macros[$name])) {
			$cdata = $this->htmlNode && in_array(strtolower($this->htmlNode->name), array('script', 'style'));
			throw new CompileException("Unknown macro {{$name}}" . ($cdata ? " (in JavaScript or CSS, try to put a space after bracket.)" : ''));
		}
		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNode, $nPrefix === NULL ? NULL : $this->htmlNode, $nPrefix);
			if ($macro->nodeOpened($node) !== FALSE) {
				return $node;
			}
		}
		throw new CompileException("Unhandled macro {{$name}}");
	}

}
