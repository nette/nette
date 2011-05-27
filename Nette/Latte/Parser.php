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
 * Compile-time filter Latte.
 *
 * @author     David Grudl
 */
class Parser extends Nette\Object
{
	/** regular expression for single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal special HTML tag or attribute prefix */
	const N_PREFIX = 'n:';

	/** @var Nette\Templates\ILatteHandler */
	public $handler;

	/** @var string */
	private $macroRe;

	/** @var string */
	private $input, $output;

	/** @var int */
	private $offset;

	/** @var strng (for CONTEXT_ATTRIBUTE) */
	private $quote;

	/** @var array */
	public $macros;

	/** @var array */
	private $htmlNodes;

	/** @var array */
	private $macroNodes = array();

	/** @var string */
	public $context = Parser::CONTEXT_NONE;

	/** @var string  context-aware escaping function */
	public $escape;

	/** @internal Context-aware escaping states */
	const CONTEXT_TEXT = 'text',
		CONTEXT_CDATA = 'cdata',
		CONTEXT_TAG = 'tag',
		CONTEXT_ATTRIBUTE = 'attribute',
		CONTEXT_NONE = 'none',
		CONTEXT_COMMENT = 'comment';



	/**
	 * Process all {macros} and <tags/>.
	 * @param  string
	 * @return string
	 */
	public function parse($s)
	{
		if (!Strings::checkEncoding($s)) {
			throw new ParseException('Template is not valid UTF-8 stream.');
		}
		if (!$this->macroRe) {
			$this->setDelimiters('\\{(?![\\s\'"{}*])', '\\}');
		}
		$this->handler->initialize($this);

		$s = str_replace("\r\n", "\n", $s);
		$s = "\n" . $s;

		$this->input = & $s;
		$this->offset = 0;
		$this->output = '';
		$this->htmlNodes = $this->macroNodes = array();
		$len = strlen($s);

		while ($this->offset < $len) {
			$matches = $this->{"context$this->context"}();

			if (!$matches) { // EOF
				break;

			} elseif (!empty($matches['comment'])) { // {* *}

			} elseif (!empty($matches['macro'])) { // {macro}
				list($macroName, $macroArgs, $macroModifiers) = $this->parseMacro($matches['macro']);
				$code = $this->macro($macroName, $macroArgs, $macroModifiers);
				$nl = isset($matches['newline']) ? "\n" : '';
				if ($nl && $matches['indent'] && strncmp($code, '<?php echo ', 11)) { // the only macro on line "without" output
					$this->output .= "\n" . $code; // preserve new line from 'indent', remove indentation
				} else {
					// double newline to avoid newline eating by PHP
					$this->output .= $matches['indent'] . $code . (substr($code, -2) === '?>' && $this->output !== '' ? $nl : '');
				}

			} else { // common behaviour
				$this->output .= $matches[0];
			}
		}

		$this->output .= substr($this->input, $this->offset);

		foreach ($this->htmlNodes as $node) {
			if (!empty($node->attrs)) {
				throw new ParseException("Missing end tag </$node->name> for macro-attribute " . self::N_PREFIX
					. implode(' and ' . self::N_PREFIX, array_keys($node->attrs)) . ".", 0, $this->line);
			}
		}

		$this->handler->finalize($this->output);

		if ($this->macroNodes) {
			throw new ParseException("There are unclosed macros.", 0, $this->line);
		}

		return $this->output;
	}



	/**
	 * Handles CONTEXT_TEXT.
	 */
	private function contextText()
	{
		$matches = $this->match('~
			(?:(?<=\n)[ \t]*)?<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
			<(?P<htmlcomment>!--)|           ##  begin of HTML comment <!--
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['htmlcomment'])) { // <!--
			$this->context = self::CONTEXT_COMMENT;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtmlComment';

		} elseif (empty($matches['closing'])) { // <tag
			$this->htmlNodes[] = $node = new HtmlNode($matches['tag']);
			$node->offset = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';

		} else { // </tag
			do {
				$node = array_pop($this->htmlNodes);
				if (!$node) {
					$node = new HtmlNode($matches['tag']);
				}
			} while (strcasecmp($node->name, $matches['tag']));
			$this->htmlNodes[] = $node;
			$node->closing = TRUE;
			$node->offset = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_CDATA.
	 */
	private function contextCData()
	{
		$node = end($this->htmlNodes);
		$matches = $this->match('~
			</'.$node->name.'(?![a-z0-9:])| ##  end HTML tag </tag
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // </tag
			$node->closing = TRUE;
			$node->offset = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_TAG.
	 */
	private function contextTag()
	{
		$matches = $this->match('~
			(?P<end>\ ?/?>)(?P<tagnewline>[\ \t]*(?=\n))?|  ##  end of HTML tag
			'.$this->macroRe.'|          ##  curly tag
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## begin of HTML attribute
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['end'])) { // end of HTML tag />
			$node = end($this->htmlNodes);
			$isEmpty = !$node->closing && (strpos($matches['end'], '/') !== FALSE || $node->isEmpty);

			if ($isEmpty) {
				$matches[0] = (Nette\Utils\Html::$xhtml ? ' />' : '>')
					. (isset($matches['tagnewline']) ? $matches['tagnewline'] : '');
			}

			if (!empty($node->attrs)) {
				$code = substr($this->output, $node->offset) . $matches[0] . (isset($matches['tagnewline']) ? "\n" : '');
				$code = $this->attrsMacro($code, $node->attrs, $node->closing);
				if ($isEmpty) {
					$code = $this->attrsMacro($code, $node->attrs, TRUE);
				}
				$this->output = substr_replace($this->output, $code, $node->offset);
				$matches[0] = ''; // remove from output
			}

			if ($isEmpty) {
				$node->closing = TRUE;
			}

			if (!$node->closing && (strcasecmp($node->name, 'script') === 0 || strcasecmp($node->name, 'style') === 0)) {
				$this->context = self::CONTEXT_CDATA;
				$this->escape = 'Nette\Templating\DefaultHelpers::escape' . (strcasecmp($node->name, 'style') ? 'Js' : 'Css');
			} else {
				$this->context = self::CONTEXT_TEXT;
				$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml|';
				if ($node->closing) {
					array_pop($this->htmlNodes);
				}
			}

		} else { // HTML attribute
			$name = $matches['attr'];
			$value = isset($matches['value']) ? $matches['value'] : '';
			$node = end($this->htmlNodes);

			if (Strings::startsWith($name, self::N_PREFIX)) {
				$name = substr($name, strlen(self::N_PREFIX));
				if ($value === '"' || $value === "'") {
					if ($matches = $this->match('~(.*?)' . $value . '~xsi')) { // overwrites $matches
						$value = $matches[1];
					}
				}
				$node->attrs[$name] = $value;
				$matches[0] = ''; // remove from output

			} elseif ($value === '"' || $value === "'") { // attribute = "'
				$this->context = self::CONTEXT_ATTRIBUTE;
				$this->quote = $value;
				$this->escape = strncasecmp($name, 'on', 2)
					? ('Nette\Templating\DefaultHelpers::escapeHtml' . (strcasecmp($name, 'style') ? "|$value" : 'Css'))
					: "Nette\\Templating\\DefaultHelpers::escapeHtmlJs|$value";
			}
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_ATTRIBUTE.
	 */
	private function contextAttribute()
	{
		$matches = $this->match('~
			(' . $this->quote . ')|      ##  1) end of HTML attribute
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // (attribute end) '"
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_COMMENT.
	 */
	private function contextComment()
	{
		$matches = $this->match('~
			(--\s*>)|                    ##  1) end of HTML comment
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // --\s*>
			$this->context = self::CONTEXT_TEXT;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml|';
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_NONE.
	 */
	private function contextNone()
	{
		$matches = $this->match('~
			'.$this->macroRe.'           ##  curly tag
		~xsi');
		return $matches;
	}



	/**
	 * Matches next token.
	 * @param  string
	 * @return array
	 */
	private function match($re)
	{
		if ($matches = Strings::match($this->input, $re, PREG_OFFSET_CAPTURE, $this->offset)) {
			$this->output .= substr($this->input, $this->offset, $matches[0][1] - $this->offset);
			$this->offset = $matches[0][1] + strlen($matches[0][0]);
			foreach ($matches as $k => $v) $matches[$k] = $v[0];
		}
		return $matches;
	}



	/**
	 * Returns current line number.
	 * @return int
	 */
	public function getLine()
	{
		return substr_count($this->input, "\n", 0, $this->offset);
	}



	/**
	 * Changes macro delimiters.
	 * @param  string  left regular expression
	 * @param  string  right regular expression
	 * @return Engine  provides a fluent interface
	 */
	public function setDelimiters($left, $right)
	{
		$this->macroRe = '
			(?:\n?)(?P<comment>\\{\\*.*?\\*\\}\n{0,2})|
			(?P<indent>\n[\ \t]*)?
			' . $left . '
				(?P<macro>(?:' . self::RE_STRING . '|[^\'"]+?)*?)
			' . $right . '
			(?P<newline>[\ \t]*(?=\n))?
		';
		return $this;
	}



	/********************* macros ****************d*g**/



	/**
	 * Expands macro and appends new node.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function macro($name, $args = '', $modifiers = '')
	{
		if (!isset($this->macros[$name])) {
			throw new ParseException("Unknown macro {{$name}}", 0, $this->line);
		}

		$closing = $name[0] === '/';
		if ($closing) {
			$node = array_pop($this->macroNodes);
			if (!$node || "/$node->name" !== $name
				|| ($args && !Strings::startsWith("$node->args ", "$args ")) || $modifiers
			) {
				$name .= $args ? ' ' : '';
				throw new ParseException("Unexpected macro {{$name}{$args}{$modifiers}}"
					. ($node ? ", expecting {/$node->name}" . ($args && $node->args ? " or eventually {/$node->name $node->args}" : '') : ''),
					0, $this->line);
			}
			$node->args = $node->modifiers = ''; // back compatibility

		} else {
			$node = new MacroNode($name, $args, $modifiers);
			if (isset($this->macros["/$name"])) {
				$node->isEmpty = TRUE;
				$this->macroNodes[] = $node;
			}
		}

		$handler = $this->handler;
		return Strings::replace(
			$this->macros[$name],
			'#%(.*?)%#',
			/*5.2* callback(*/function ($m) use ($handler, $node) {
				if ($m[1]) {
					return callback($m[1][0] === ':' ? array($handler, substr($m[1], 1)) : $m[1])
						->invoke($node->args, $node->modifiers);
				} else {
					return $handler->writer->formatArgs($node->args);
				}
			}/*5.2* )*/
		);
	}



	/**
	 * Expands macro <tag n:attr> and appends new node.
	 * @param  string
	 * @param  array
	 * @param  bool
	 * @return string
	 */
	public function attrsMacro($code, $attrs, $closing)
	{
		foreach ($attrs as $name => $args) {
			if (substr($name, 0, 5) === 'attr-') {
				if (!$closing) {
					$pos = strrpos($code, '>');
					if ($code[$pos-1] === '/') {
						$pos--;
					}
					$code = substr_replace($code, str_replace('@@', substr($name, 5), $this->macro("@attr", $args)), $pos, 0);
				}
				unset($attrs[$name]);
			}
		}

		$left = $right = array();
		foreach ($this->macros as $name => $foo) {
			if ($name[0] === '@') {
				$name = substr($name, 1);
				if (isset($attrs[$name])) {
					if (!$closing) {
						$pos = strrpos($code, '>');
						if ($code[$pos-1] === '/') {
							$pos--;
						}
						$code = substr_replace($code, $this->macro("@$name", $attrs[$name]), $pos, 0);
					}
					unset($attrs[$name]);
				}
			}

			if (!isset($this->macros["/$name"])) { // must be pair-macro
				continue;
			}

			$macro = $closing ? "/$name" : $name;
			if (isset($attrs[$name])) {
				if ($closing) {
					$right[] = array($macro, '');
				} else {
					array_unshift($left, array($macro, $attrs[$name]));
				}
			}

			$innerName = "inner-$name";
			if (isset($attrs[$innerName])) {
				if ($closing) {
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
			throw new ParseException("Unknown macro-attribute " . self::N_PREFIX
				. implode(' and ' . self::N_PREFIX, array_keys($attrs)), 0, $this->line);
		}
		$s = '';
		foreach ($left as $item) {
			$m = $this->macro($item[0], $item[1]);
			$s .= $m . (substr($m, -2) === '?>' ? "\n" : '');
		}
		$s .= $code;
		foreach ($right as $item) {
			$m = $this->macro($item[0], $item[1]);
			$s .= $m . (substr($m, -2) === '?>' ? "\n" : '');
		}
		$s = rtrim($s, "\n");
		return $s;
	}



	/**
	 * Parses macro to name, arguments a modifiers parts.
	 * @param  string {name arguments | modifiers}
	 * @return array
	 */
	public function parseMacro($macro)
	{
		$match = Strings::match($macro, '~^
			(
				(?P<name>\?|/?[a-z]++(?:[.:][a-z0-9]+)*+(?!::|\())|   ## ?, name, /name, but not function( or class::
				(?P<noescape>!?)(?P<shortname>[=\~#%^&_]?)            ## [!] [=] expression to print
			)(?P<args>.*?)
			(?P<modifiers>\|[a-z](?:'.Parser::RE_STRING.'|[^\'"]+)*)?
		()$~isx');

		if (!$match) {
			return FALSE;
		}
		if ($match['name'] === '') {
			$match['name'] = $match['shortname'] ?: '=';
			if (!$match['noescape']) {
				$match['modifiers'] .= '|escape';
			}
		}
		return array($match['name'], trim($match['args']), $match['modifiers']);
	}

}
