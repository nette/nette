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
	/** @internal regular expression for single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal special HTML tag or attribute prefix */
	const N_PREFIX = 'n:';

	/** @var string */
	private $macroRe;

	/** @var string source template */
	private $input;

	/** @var string output code */
	private $output;

	/** @var int  position on source template */
	private $offset;

	/** @var array of [name => array of IMacro] */
	private $macros;

	/** @var SplObjectStorage */
	private $macroHandlers;

	/** @var array of HtmlNode */
	private $htmlNodes = array();

	/** @var array of MacroNode */
	private $macroNodes = array();

	/** @var array */
	public $context;

	/** @var string */
	public $templateId;

	/** @internal Context-aware escaping states */
	const CONTEXT_TEXT = 'text',
		CONTEXT_CDATA = 'cdata',
		CONTEXT_TAG = 'tag',
		CONTEXT_ATTRIBUTE = 'attribute',
		CONTEXT_NONE = 'none',
		CONTEXT_COMMENT = 'comment';



	public function __construct()
	{
		$this->macroHandlers = new \SplObjectStorage;
		$this->setDelimiters('\\{(?![\\s\'"{}])', '\\}');
		$this->context = array(self::CONTEXT_NONE, 'text');
	}



	/**
	 * Adds new macro
	 * @param
	 * @return Parser  provides a fluent interface
	 */
	public function addMacro($name, IMacro $macro)
	{
		$this->macros[$name][] = $macro;
		$this->macroHandlers->attach($macro);
		return $this;
	}



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
		$s = str_replace("\r\n", "\n", $s);

		$this->templateId = Strings::random();
		$this->input = & $s;
		$this->offset = 0;
		$this->output = '';
		$this->htmlNodes = $this->macroNodes = array();

		foreach ($this->macroHandlers as $handler) {
			$handler->initialize($this);
		}

		$len = strlen($s);

		try {
			while ($this->offset < $len) {
				$matches = $this->{"context".$this->context[0]}();

				if (!$matches) { // EOF
					break;

				} elseif (!empty($matches['comment'])) { // {* *}

				} elseif (!empty($matches['macro'])) { // {macro}
					list($macroName, $macroArgs, $macroModifiers) = $this->parseMacro($matches['macro']);
					$isRightmost = $this->offset >= $len || $this->input[$this->offset] === "\n";
					$this->writeMacro($macroName, $macroArgs, $macroModifiers, $isRightmost);

				} else { // common behaviour
					$this->output .= $matches[0];
				}
			}
		} catch (ParseException $e) {
			if (!$e->sourceLine) {
				$e->sourceLine = $this->getLine();
			}
			throw $e;
		}

		$this->output .= substr($this->input, $this->offset);

		foreach ($this->htmlNodes as $node) {
			if (!empty($node->attrs)) {
				throw new ParseException("Missing end tag </$node->name> for macro-attribute " . self::N_PREFIX
					. implode(' and ' . self::N_PREFIX, array_keys($node->attrs)) . ".", 0, $this->getLine());
			}
		}

		$prologs = $epilogs = '';
		foreach ($this->macroHandlers as $handler) {
			$res = $handler->finalize();
			$prologs .= isset($res[0]) ? "<?php $res[0]\n?>" : '';
			$epilogs .= isset($res[1]) ? "<?php $res[1]\n?>" : '';
		}
		$this->output = ($prologs ? $prologs . "<?php\n//\n// main template\n//\n?>\n" : '') . $this->output . $epilogs;

		if ($this->macroNodes) {
			throw new ParseException("There are unclosed macros.", 0, $this->getLine());
		}

		return $this->output;
	}



	/**
	 * Handles CONTEXT_TEXT.
	 */
	private function contextText()
	{
		$matches = $this->match('~
			(?:(?<=\n|^)[ \t]*)?<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
			<(?P<htmlcomment>!--)|           ##  begin of HTML comment <!--
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['htmlcomment'])) { // <!--
			$this->context = array(self::CONTEXT_COMMENT);

		} elseif (empty($matches['closing'])) { // <tag
			$this->htmlNodes[] = $node = new HtmlNode($matches['tag']);
			$node->offset = strlen($this->output);
			$this->context = array(self::CONTEXT_TAG);

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
			$this->context = array(self::CONTEXT_TAG);
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
			$this->context = array(self::CONTEXT_TAG);
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_TAG.
	 */
	private function contextTag()
	{
		$matches = $this->match('~
			(?P<end>\ ?/?>)(?P<tagnewline>[ \t]*\n)?|  ##  end of HTML tag
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
				$code = substr($this->output, $node->offset) . $matches[0];
				$this->output = substr($this->output, 0, $node->offset);
				$this->writeAttrsMacro($code, $node->attrs, $node->closing);
				if ($isEmpty) {
					$this->writeAttrsMacro('', $node->attrs, TRUE);
				}
				$matches[0] = ''; // remove from output
			}

			if ($isEmpty) {
				$node->closing = TRUE;
			}

			if (!$node->closing && (strcasecmp($node->name, 'script') === 0 || strcasecmp($node->name, 'style') === 0)) {
				$this->context = array(self::CONTEXT_CDATA, strcasecmp($node->name, 'style') ? 'js' : 'css');
			} else {
				$this->context = array(self::CONTEXT_TEXT);
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
				$this->context = array(self::CONTEXT_ATTRIBUTE, $name, $value);
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
			(' . $this->context[2] . ')|      ##  1) end of HTML attribute
			'.$this->macroRe.'                ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // (attribute end) '"
			$this->context = array(self::CONTEXT_TAG);
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
			$this->context = array(self::CONTEXT_TEXT);
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
		return $this->input && $this->offset ? substr_count($this->input, "\n", 0, $this->offset - 1) + 1 : NULL;
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
			(?P<comment>' . $left . '\\*.*?\\*' . $right . '\n{0,2})|
			' . $left . '
				(?P<macro>(?:' . self::RE_STRING . '|[^\'"]+?)*?)
			' . $right . '
			(?P<rmargin>[ \t]*(?=\n))?
		';
		return $this;
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
					. ($node ? ", expecting {/$node->name}" . ($args && $node->args ? " or eventually {/$node->name $node->args}" : '') : ''),
					0, $this->getLine());
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
	public function writeAttrsMacro($code, $attrs, $closing)
	{
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
						list(, $macroCode) = $this->expandMacro("@$name", $attrs[$name]);
						$code = substr_replace($code, $macroCode, $pos, 0);
					}
					unset($attrs[$name]);
				}
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
				. implode(' and ' . self::N_PREFIX, array_keys($attrs)), 0, $this->getLine());
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
	public function expandMacro($name, $args, $modifiers = NULL)
	{
		if (empty($this->macros[$name])) {
			throw new ParseException("Unknown macro {{$name}}", 0, $this->getLine());
		}
		foreach (array_reverse($this->macros[$name]) as $macro) {
			$node = new MacroNode($macro, $name, $args, $modifiers, $this->macroNodes ? end($this->macroNodes) : NULL);
			$code = $macro->nodeOpened($node);
			if ($code !== FALSE) {
				return array($node, $code);
			}
		}
		throw new ParseException("Unhandled macro {{$name}}", 0, $this->getLine());
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
				(?P<name>\?|/?[a-z]\w*+(?:[.:]\w+)*+(?!::|\())|   ## ?, name, /name, but not function( or class::
				(?P<noescape>!?)(?P<shortname>/?[=\~#%^&_]?)      ## [!] [=] expression to print
			)(?P<args>.*?)
			(?P<modifiers>\|[a-z](?:'.Parser::RE_STRING.'|[^\'"]+)*)?
		()$~isx');

		if (!$match) {
			return FALSE;
		}
		if ($match['name'] === '') {
			$match['name'] = $match['shortname'] ?: '=';
			if (!$match['noescape'] && substr($match['shortname'], 0, 1) !== '/') {
				$match['modifiers'] .= '|escape';
			}
		}
		return array($match['name'], trim($match['args']), $match['modifiers']);
	}

}
