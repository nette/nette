<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
	public $defaultContentType = self::CONTENT_HTML;

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

	/** @var string[] */
	private $attrCodes = array();

	/** @var string */
	private $contentType;

	/** @var array [context, subcontext] */
	private $context;

	/** @var string */
	private $templateId;

	/** Context-aware escaping content types */
	const CONTENT_HTML = 'html',
		CONTENT_XHTML = 'xhtml',
		CONTENT_XML = 'xml',
		CONTENT_JS = 'js',
		CONTENT_CSS = 'css',
		CONTENT_URL = 'url',
		CONTENT_ICAL = 'ical',
		CONTENT_TEXT = 'text';

	/** @internal Context-aware escaping HTML contexts */
	const CONTEXT_COMMENT = 'comment',
		CONTEXT_SINGLE_QUOTED_ATTR = "'",
		CONTEXT_DOUBLE_QUOTED_ATTR = '"',
		CONTEXT_UNQUOTED_ATTR = '=';


	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
	}


	/**
	 * Adds new macro.
	 * @param  string
	 * @return self
	 */
	public function addMacro($name, IMacro $macro)
	{
		$this->macros[$name][] = $macro;
		$this->macroHandlers->attach($macro);
		return $this;
	}


	/**
	 * Compiles tokens to PHP code.
	 * @param  Token[]
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
				throw new CompileException('Missing ' . self::printEndTag($this->macroNode), 0, $token->line);
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
			throw new CompileException('Missing ' . self::printEndTag($this->macroNode), 0, $token->line);
		}

		$output = $this->expandTokens($output);
		return $output;
	}


	/**
	 * @return self
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
	 * @return self
	 */
	public function setContext($context, $sub = NULL)
	{
		$this->context = array($context, $sub);
		return $this;
	}


	/**
	 * @return array [context, subcontext]
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
		if (($this->context[0] === self::CONTEXT_SINGLE_QUOTED_ATTR || $this->context[0] === self::CONTEXT_DOUBLE_QUOTED_ATTR)
			&& $token->text === $this->context[0]
		) {
			$this->setContext(self::CONTEXT_UNQUOTED_ATTR);
		}
		$this->output .= $token->text;
	}


	private function processMacroTag(Token $token)
	{
		$isRightmost = !isset($this->tokens[$this->position + 1])
			|| substr($this->tokens[$this->position + 1]->text, 0, 1) === "\n";

		if ($token->name[0] === '/') {
			$this->closeMacro((string) substr($token->name, 1), $token->value, $token->modifiers, $isRightmost);
		} else {
			$this->openMacro($token->name, $token->value, $token->modifiers, $isRightmost && !$token->empty);
			if ($token->empty) {
				$this->closeMacro($token->name, NULL, NULL, $isRightmost);
			}
		}
	}


	private function processHtmlTagBegin(Token $token)
	{
		if ($token->closing) {
			while ($this->htmlNode) {
				if (strcasecmp($this->htmlNode->name, $token->name) === 0) {
					break;
				}
				if ($this->htmlNode->macroAttrs) {
					throw new CompileException("Unexpected </$token->name>, expecting " . self::printEndTag($this->macroNode));
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
			$this->setContext(self::CONTEXT_UNQUOTED_ATTR);
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
		$end = '';

		if ($isEmpty && in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))) { // auto-correct
			$token->text = preg_replace('#^.*>#', $htmlNode->isEmpty && $this->contentType === self::CONTENT_XHTML ? ' />' : '>', $token->text);
			if (!$htmlNode->isEmpty) {
				$end = "</$htmlNode->name>";
			}
		}

		if (empty($htmlNode->macroAttrs)) {
			$this->output .= $token->text . $end;
		} else {
			$code = substr($this->output, $htmlNode->offset) . $token->text;
			$this->output = substr($this->output, 0, $htmlNode->offset);
			$this->writeAttrsMacro($code);
			if ($isEmpty) {
				$htmlNode->closing = TRUE;
				$this->writeAttrsMacro($end);
			}
		}

		if ($isEmpty) {
			$htmlNode->closing = TRUE;
		}

		$lower = strtolower($htmlNode->name);
		if (!$htmlNode->closing && ($lower === 'script' || $lower === 'style')) {
			$this->setContext($lower === 'script' ? self::CONTENT_JS : self::CONTENT_CSS);
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
			$name = substr($token->name, strlen(Parser::N_PREFIX));
			if (isset($this->htmlNode->macroAttrs[$name])) {
				throw new CompileException("Found multiple macro-attributes $token->name.");

			} elseif ($this->macroNode && $this->macroNode->htmlNode === $this->htmlNode) {
				throw new CompileException("Macro-attributes must not appear inside macro; found $token->name inside {{$this->macroNode->name}}.");
			}
			$this->htmlNode->macroAttrs[$name] = $token->value;
			return;
		}

		$this->htmlNode->attrs[$token->name] = TRUE;
		$this->output .= $token->text;

		$context = NULL;
		if (in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))) {
			$lower = strtolower($token->name);
			if (substr($lower, 0, 2) === 'on') {
				$context = self::CONTENT_JS;
			} elseif ($lower === 'style') {
				$context = self::CONTENT_CSS;
			} elseif (in_array($lower, array('href', 'src', 'action', 'formaction'))
				|| ($lower === 'data' && strtolower($this->htmlNode->name) === 'object')
			) {
				$context = self::CONTENT_URL;
			}
		}
		$this->setContext($token->value ?: self::CONTEXT_UNQUOTED_ATTR, $context);
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
	public function openMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE, $nPrefix = NULL)
	{
		$node = $this->expandMacro($name, $args, $modifiers, $nPrefix);
		if ($node->isEmpty) {
			$this->writeCode($node->openingCode, $this->output, $isRightmost);
		} else {
			$this->macroNode = $node;
			$node->saved = array(& $this->output, $isRightmost);
			$this->output = & $node->content;
		}
		return $node;
	}


	/**
	 * Generates code for {/macro ...} to the output.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return MacroNode
	 */
	public function closeMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE, $nPrefix = NULL)
	{
		$node = $this->macroNode;

		if (!$node || ($node->name !== $name && '' !== $name) || $modifiers
			|| ($args && $node->args && !Strings::startsWith("$node->args ", "$args "))
			|| $nPrefix !== $node->prefix
		) {
			$name = $nPrefix
				? "</{$this->htmlNode->name}> for macro-attribute " . Parser::N_PREFIX . implode(' and ' . Parser::N_PREFIX, array_keys($this->htmlNode->macroAttrs))
				: '{/' . $name . ($args ? ' ' . $args : '') . $modifiers . '}';
			throw new CompileException("Unexpected $name" . ($node ? ', expecting ' . self::printEndTag($node) : ''));
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

		foreach ($this->macros as $name => $foo) {
			$attrName = MacroNode::PREFIX_INNER . "-$name";
			if (isset($attrs[$attrName])) {
				if ($this->htmlNode->closing) {
					$left[] = array('closeMacro', $name, '', MacroNode::PREFIX_INNER);
				} else {
					array_unshift($right, array('openMacro', $name, $attrs[$attrName], MacroNode::PREFIX_INNER));
				}
				unset($attrs[$attrName]);
			}
		}

		foreach (array_reverse($this->macros) as $name => $foo) {
			$attrName = MacroNode::PREFIX_TAG . "-$name";
			if (isset($attrs[$attrName])) {
				$left[] = array('openMacro', $name, $attrs[$attrName], MacroNode::PREFIX_TAG);
				array_unshift($right, array('closeMacro', $name, '', MacroNode::PREFIX_TAG));
				unset($attrs[$attrName]);
			}
		}

		foreach ($this->macros as $name => $foo) {
			if (isset($attrs[$name])) {
				if ($this->htmlNode->closing) {
					$right[] = array('closeMacro', $name, '', MacroNode::PREFIX_NONE);
				} else {
					array_unshift($left, array('openMacro', $name, $attrs[$name], MacroNode::PREFIX_NONE));
				}
				unset($attrs[$name]);
			}
		}

		if ($attrs) {
			throw new CompileException('Unknown macro-attribute ' . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($attrs)));
		}

		if (!$this->htmlNode->closing) {
			$this->htmlNode->attrCode = & $this->attrCodes[$uniq = ' n:' . Nette\Utils\Strings::random()];
			$code = substr_replace($code, $uniq, strrpos($code, '/>') ?: strrpos($code, '>'), 0);
		}

		foreach ($left as $item) {
			$node = $this->{$item[0]}($item[1], $item[2], NULL, NULL, $item[3]);
			if ($node->closing || $node->isEmpty) {
				$this->htmlNode->attrCode .= $node->attrCode;
				if ($node->isEmpty) {
					unset($this->htmlNode->macroAttrs[$node->name]);
				}
			}
		}

		$this->output .= $code;

		foreach ($right as $item) {
			$node = $this->{$item[0]}($item[1], $item[2], NULL, NULL, $item[3]);
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
		$inScript = in_array($this->context[0], array(self::CONTENT_JS, self::CONTENT_CSS));

		if (empty($this->macros[$name])) {
			throw new CompileException("Unknown macro {{$name}}" . ($inScript ? ' (in JavaScript or CSS, try to put a space after bracket.)' : ''));
		}

		if ($this->context[1] === self::CONTENT_URL) {
			$modifiers = preg_replace('#\|nosafeurl\s?(?=\||\z)#i', '', $modifiers, -1, $found);
			if (!$found && !preg_match('#\|datastream(?=\s|\||\z)#i', $modifiers)) {
				$modifiers .= '|safeurl';
			}
		}

		$modifiers = preg_replace('#\|noescape\s?(?=\||\z)#i', '', $modifiers, -1, $found);
		if (!$found && strpbrk($name, '=~%^&_')) {
			$modifiers .= '|escape';
		}

		if (!$found && $inScript && $name === '=' && preg_match('#["\'] *\z#', $this->tokens[$this->position - 1]->text)) {
			throw new CompileException("Do not place {$this->tokens[$this->position]->text} inside quotes.");
		}

		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNode, $this->htmlNode, $nPrefix);
			if ($macro->nodeOpened($node) !== FALSE) {
				return $node;
			}
		}
		throw new CompileException("Unhandled macro {{$name}}");
	}


	private static function printEndTag(MacroNode $node)
	{
		if ($node->prefix) {
			return  "</{$node->htmlNode->name}> for macro-attribute " . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($node->htmlNode->macroAttrs));
		} else {
			return "{/$node->name}";
		}
	}

}
