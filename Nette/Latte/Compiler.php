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
	/** @var string */
	private $macroRe;

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

	/** @var array */
	private $context;

	/** @var string */
	private $templateId;

	/** @internal Context-aware escaping states */
	const CONTEXT_NONE = 'none',
		CONTEXT_HTML = 'html',
		CONTEXT_HTML_JS = 'html_js',
		CONTEXT_HTML_CSS = 'html_css',
		CONTEXT_HTML_COMMENT = 'html_comment',
		CONTEXT_XML = 'xml',
		CONTEXT_JS = 'js',
		CONTEXT_CSS = 'css',
		CONTEXT_ICAL = 'ical';



	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
		$this->setContext(self::CONTEXT_HTML);
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


		foreach ($this->htmlNodes as $node) {
			if (!empty($node->macroAttrs)) {
				throw new ParseException("Missing end tag </$node->name> for macro-attribute " . Parser::N_PREFIX
					. implode(' and ' . Parser::N_PREFIX, array_keys($node->macroAttrs)) . ".", 0, $token->line);
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
	public function setContext($context, $spec = NULL)
	{
		$this->context = array($context, $spec);
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
				$node = array_pop($this->htmlNodes);
				if (!$node) {
					$node = new HtmlNode($token->name);
				}
			} while (strcasecmp($node->name, $token->name));
			$this->htmlNodes[] = $node;
			$node->closing = TRUE;
			$node->offset = strlen($this->output);
			$this->setContext(self::CONTEXT_HTML);

		} elseif ($token->text === '<!--') {
			$this->setContext(self::CONTEXT_HTML_COMMENT);

		} else {
			$this->htmlNodes[] = $node = new HtmlNode($token->name);
			$node->offset = strlen($this->output);
			$this->setContext(self::CONTEXT_HTML);
		}
		$this->output .= $token->text;
	}



	private function processTagEnd($token)
	{
		if ($token->text === '-->') {
			$this->output .= $token->text;
			$this->setContext(self::CONTEXT_HTML);
			return;
		}

		$node = end($this->htmlNodes);
		$isEmpty = !$node->closing && (Strings::contains($token->text, '/') || $node->isEmpty);

		if ($isEmpty) {
			$token->text = preg_replace('#^.*>#', Nette\Utils\Html::$xhtml ? ' />' : '>', $token->text);
		}

		if (empty($node->macroAttrs)) {
			$this->output .= $token->text;
		} else {
			$code = substr($this->output, $node->offset) . $token->text;
			$this->output = substr($this->output, 0, $node->offset);
			$this->writeAttrsMacro($code, $node);
			if ($isEmpty) {
				$node->closing = TRUE;
				$this->writeAttrsMacro('', $node);
			}
		}

		if ($isEmpty) {
			$node->closing = TRUE;
		}

		if (!$node->closing && (strcasecmp($node->name, 'script') === 0 || strcasecmp($node->name, 'style') === 0)) {
			$this->setContext(strcasecmp($node->name, 'style') ? self::CONTEXT_JS : self::CONTEXT_CSS);
		} else {
			$this->setContext(self::CONTEXT_HTML);
			if ($node->closing) {
				array_pop($this->htmlNodes);
			}
		}
	}



	private function processAttribute($token)
	{
		$node = end($this->htmlNodes);
		if (Strings::startsWith($token->name, Parser::N_PREFIX)) {
			$node->macroAttrs[substr($token->name, strlen(Parser::N_PREFIX))] = $token->value;
		} else {
			$node->attrs[$token->name] = TRUE; // TODO: nebo hodnotu?
			$this->output .= $token->text;
			if ($token->value) { // quoted
				$context = self::CONTEXT_HTML;
				if (strncasecmp($token->name, 'on', 2) === 0) {
					$context = self::CONTEXT_HTML_JS;
				} elseif ($token->name === 'style') {
					$context = self::CONTEXT_HTML_CSS;
				}
				$this->setContext($context, $token->value);
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

			$code = $node->close(substr($this->output, $node->offset));

			if (!$isLeftmost && $isRightmost && substr($code, -2) === '?>') {
				$code .= "\n"; // double newline to avoid newline eating by PHP
			}
			$this->output = substr($this->output, 0, $node->offset) . $node->content. $code;

		} else { // opening
			list($node, $code) = $this->expandMacro($name, $args, $modifiers);
			if (!$node->isEmpty) {
				$this->macroNodes[] = $node;
			}

			if ($isRightmost) {
				if ($isLeftmost && substr($code, 0, 11) !== '<?php echo ') {
					$this->output = substr($this->output, 0, $leftOfs); // alone macro without output -> remove indentation
				} elseif (substr($code, -2) === '?>') {
					$code .= "\n"; // double newline to avoid newline eating by PHP
				}
			}

			$this->output .= $code;
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
	public function writeAttrsMacro($code, HtmlNode $node)
	{
		$attrs = $node->macroAttrs;
		$left = $right = array();
		foreach ($this->macros as $name => $foo) {
			if ($name[0] === '@') { // attribute macro
				$name = substr($name, 1);
				if (!isset($attrs[$name])) {
					continue;
				}
				if (!$node->closing) {
					$pos = strrpos($code, '>');
					if ($code[$pos-1] === '/') {
						$pos--;
					}
					$this->setContext(self::CONTEXT_HTML, '"');
					list(, $macroCode) = $this->expandMacro("@$name", $attrs[$name], NULL, $node);
					$this->setContext(self::CONTEXT_HTML);
					$code = substr_replace($code, $macroCode, $pos, 0);
				}
				unset($attrs[$name]);
			}

			$macro = $node->closing ? "/$name" : $name;
			if (isset($attrs[$name])) {
				if ($node->closing) {
					$right[] = array($macro, '');
				} else {
					array_unshift($left, array($macro, $attrs[$name]));
				}
			}

			$innerName = "inner-$name";
			if (isset($attrs[$innerName])) {
				if ($node->closing) {
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
	 * @return array(MacroNode, string)
	 */
	public function expandMacro($name, $args, $modifiers = NULL, HtmlNode $htmlNode = NULL)
	{
		if (empty($this->macros[$name])) {
			throw new ParseException("Unknown macro {{$name}}");
		}
		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNodes ? end($this->macroNodes) : NULL, $htmlNode);
			$code = $macro->nodeOpened($node);
			if ($code !== FALSE) {
				return array($node, $code);
			}
		}
		throw new ParseException("Unhandled macro {{$name}}");
	}

}
