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

	/** @var array of Token */
	private $tokens;

	/** @var string output code */
	private $output;

	/** @var int  position on source template */
	private $position;

	/** @var array of [name => array of IMacro] */
	private $macros;

	/** @var SplObjectStorage */
	private $macroHandlers;

	/** @var array of HtmlNode */
	private $htmlNodes = array();

	/** @var array of MacroNode */
	private $macroNodes = array();

	/** @var string */
	private $contentType;

	/** @var array */
	private $context;

	/** @var string */
	private $templateId;

	/** @internal Context-aware escaping states */
	const CONTENT_HTML = 'html',
		CONTENT_XHTML = 'xhtml',
		CONTENT_XML = 'xml',
		CONTENT_JS = 'js',
		CONTENT_CSS = 'css',
		CONTENT_ICAL = 'ical',
		CONTENT_TEXT = 'text',

		CONTEXT_COMMENT = 'comment',
		CONTEXT_SINGLE_QUOTED = "'",
		CONTEXT_DOUBLE_QUOTED = '"';


	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
	}



	/**
	 * Adds new macro.
	 * @param
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
		$this->output = '';
		$this->htmlNodes = $this->macroNodes = array();
		$this->setContentType($this->defaultContentType);

		foreach ($this->macroHandlers as $handler) {
			$handler->initialize($this);
		}

		try {
			foreach ($tokens as $this->position => $token) {
				if ($token->type === Token::TEXT) {
					$this->output .= $token->text;

				} elseif ($token->type === Token::MACRO) {
					$isRightmost = !isset($tokens[$this->position + 1])
						|| substr($tokens[$this->position + 1]->text, 0, 1) === "\n";
					$this->writeMacro($token->name, $token->value, $token->modifiers, $isRightmost);

				} elseif ($token->type === Token::TAG_BEGIN) {
					$this->processTagBegin($token);

				} elseif ($token->type === Token::TAG_END) {
					$this->processTagEnd($token);

				} elseif ($token->type === Token::ATTRIBUTE) {
					$this->processAttribute($token);
				}
			}
		} catch (ParseException $e) {
			$e->sourceLine = $token->line;
			throw $e;
		}


		foreach ($this->htmlNodes as $htmlNode) {
			if (!empty($htmlNode->macroAttrs)) {
				throw new ParseException("Missing end tag </$htmlNode->name> for macro-attribute " . Parser::N_PREFIX
					. implode(' and ' . Parser::N_PREFIX, array_keys($htmlNode->macroAttrs)) . ".", 0, $token->line);
			}
		}

		$prologs = $epilogs = '';
		foreach ($this->macroHandlers as $handler) {
			$res = $handler->finalize();
			$handlerName = get_class($handler);
			$prologs .= empty($res[0]) ? '' : "<?php\n// prolog $handlerName\n$res[0]\n?>";
			$epilogs = (empty($res[1]) ? '' : "<?php\n// epilog $handlerName\n$res[1]\n?>") . $epilogs;
		}
		$this->output = ($prologs ? $prologs . "<?php\n//\n// main template\n//\n?>\n" : '') . $this->output . $epilogs;

		if ($this->macroNodes) {
			throw new ParseException("There are unclosed macros.", 0, $token->line);
		}

		return $this->output;
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
	 * Returns current line number.
	 * @return int
	 */
	public function getLine()
	{
		return $this->tokens ? $this->tokens[$this->position]->line : NULL;
	}



	private function processTagBegin($token)
	{
		if ($token->closing) {
			do {
				$htmlNode = array_pop($this->htmlNodes);
				if (!$htmlNode) {
					$htmlNode = new HtmlNode($token->name);
				}
			} while (strcasecmp($htmlNode->name, $token->name));
			$this->htmlNodes[] = $htmlNode;
			$htmlNode->closing = TRUE;
			$htmlNode->offset = strlen($this->output);
			$this->setContext(NULL);

		} elseif ($token->text === '<!--') {
			$this->setContext(self::CONTEXT_COMMENT);

		} else {
			$this->htmlNodes[] = $htmlNode = new HtmlNode($token->name);
			$htmlNode->isEmpty = in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))
				&& isset(Nette\Utils\Html::$emptyElements[strtolower($token->name)]);
			$htmlNode->offset = strlen($this->output);
			$this->setContext(NULL);
		}
		$this->output .= $token->text;
	}



	private function processTagEnd($token)
	{
		if ($token->text === '-->') {
			$this->output .= $token->text;
			$this->setContext(NULL);
			return;
		}

		$htmlNode = end($this->htmlNodes);
		$isEmpty = !$htmlNode->closing && (Strings::contains($token->text, '/') || $htmlNode->isEmpty);

		if ($isEmpty && in_array($this->contentType, array(self::CONTENT_HTML, self::CONTENT_XHTML))) { // auto-correct
			$token->text = preg_replace('#^.*>#', $this->contentType === self::CONTENT_XHTML ? ' />' : '>', $token->text);
		}

		if (empty($htmlNode->macroAttrs)) {
			$this->output .= $token->text;
		} else {
			$code = substr($this->output, $htmlNode->offset) . $token->text;
			$this->output = substr($this->output, 0, $htmlNode->offset);
			$this->writeAttrsMacro($code, $htmlNode);
			if ($isEmpty) {
				$htmlNode->closing = TRUE;
				$this->writeAttrsMacro('', $htmlNode);
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
				array_pop($this->htmlNodes);
			}
		}
	}



	private function processAttribute($token)
	{
		$htmlNode = end($this->htmlNodes);
		if (Strings::startsWith($token->name, Parser::N_PREFIX)) {
			$htmlNode->macroAttrs[substr($token->name, strlen(Parser::N_PREFIX))] = $token->value;
		} else {
			$htmlNode->attrs[$token->name] = TRUE;
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



	/********************* macros ****************d*g**/



	/**
	 * Generates code for {macro ...} to the output.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @return void
	 */
	public function writeMacro($name, $args = NULL, $modifiers = NULL, $isRightmost = FALSE)
	{
		$isLeftmost = trim(substr($this->output, $leftOfs = strrpos("\n$this->output", "\n"))) === '';

		if ($name[0] === '/') { // closing
			$node = end($this->macroNodes);

			if (!$node || ("/$node->name" !== $name && '/' !== $name) || $modifiers
				|| ($args && $node->args && !Strings::startsWith("$node->args ", "$args "))
			) {
				$name .= $args ? ' ' : '';
				throw new ParseException("Unexpected macro {{$name}{$args}{$modifiers}}"
					. ($node ? ", expecting {/$node->name}" . ($args && $node->args ? " or eventually {/$node->name $node->args}" : '') : ''));
			}

			array_pop($this->macroNodes);
			if (!$node->args) {
				$node->setArgs($args);
			}
			if ($isLeftmost && $isRightmost) {
				$this->output = substr($this->output, 0, $leftOfs); // alone macro -> remove indentation
			}

			$node->closing = TRUE;
			$node->content = substr($this->output, $node->offset);
			$node->macro->nodeClosed($node);

			if (!$isLeftmost && $isRightmost && substr($node->closingCode, -2) === '?>') {
				$node->closingCode .= "\n"; // double newline to avoid newline eating by PHP
			}
			$this->output = substr($this->output, 0, $node->offset) . $node->content. $node->closingCode;

		} else { // opening
			$node = $this->expandMacro($name, $args, $modifiers);
			if (!$node->isEmpty) {
				$this->macroNodes[] = $node;
			}

			if ($isRightmost) {
				if ($isLeftmost && substr($node->openingCode, 0, 11) !== '<?php echo ') {
					$this->output = substr($this->output, 0, $leftOfs); // alone macro without output -> remove indentation
				} elseif (substr($node->openingCode, -2) === '?>') {
					$node->openingCode .= "\n"; // double newline to avoid newline eating by PHP
				}
			}

			$this->output .= $node->openingCode;
			$node->offset = strlen($this->output);
		}
	}



	/**
	 * Generates code for macro <tag n:attr> to the output.
	 * @param  string
	 * @param  array
	 * @param  bool
	 * @return void
	 */
	public function writeAttrsMacro($code, HtmlNode $htmlNode)
	{
		$attrs = $htmlNode->macroAttrs;
		$left = $right = array();
		foreach ($this->macros as $name => $foo) {
			if ($name[0] === '@') { // attribute macro
				$name = substr($name, 1);
				if (!isset($attrs[$name])) {
					continue;
				}
				if (!$htmlNode->closing) {
					$pos = strrpos($code, '>');
					if ($code[$pos-1] === '/') {
						$pos--;
					}
					$macroNode = $this->expandMacro("@$name", $attrs[$name], NULL, $htmlNode);
					$code = substr_replace($code, $macroNode->attrCode, $pos, 0);
				}
				unset($attrs[$name]);
			}

			$macro = $htmlNode->closing ? "/$name" : $name;
			if (isset($attrs[$name])) {
				if ($htmlNode->closing) {
					$right[] = array($macro, '');
				} else {
					array_unshift($left, array($macro, $attrs[$name]));
				}
			}

			$innerName = "inner-$name";
			if (isset($attrs[$innerName])) {
				if ($htmlNode->closing) {
					$left[] = array($macro, '');
				} else {
					array_unshift($right, array($macro, $attrs[$innerName]));
				}
			}

			$tagName = "tag-$name";
			if (isset($attrs[$tagName])) {
				array_unshift($left, array($name, $attrs[$tagName]));
				$right[] = array("/$name", '');
			}

			unset($attrs[$name], $attrs[$innerName], $attrs[$tagName]);
		}

		if ($attrs) {
			throw new ParseException("Unknown macro-attribute " . Parser::N_PREFIX
				. implode(' and ' . Parser::N_PREFIX, array_keys($attrs)));
		}

		foreach ($left as $item) {
			$this->writeMacro($item[0], $item[1]);
			if (substr($this->output, -2) === '?>') {
				$this->output .= "\n";
			}
		}
		$this->output .= $code;

		foreach ($right as $item) {
			$this->writeMacro($item[0], $item[1]);
			if (substr($this->output, -2) === '?>') {
				$this->output .= "\n";
			}
		}
	}



	/**
	 * Expands macro and returns node & code.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return MacroNode
	 */
	public function expandMacro($name, $args, $modifiers = NULL, HtmlNode $htmlNode = NULL)
	{
		if (empty($this->macros[$name])) {
			throw new ParseException("Unknown macro {{$name}}");
		}
		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNodes ? end($this->macroNodes) : NULL, $htmlNode);
			if ($macro->nodeOpened($node) !== FALSE) {
				return $node;
			}
		}
		throw new ParseException("Unhandled macro {{$name}}");
	}

}
