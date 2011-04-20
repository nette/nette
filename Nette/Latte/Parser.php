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
	const HTML_PREFIX = 'n:';

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
	private $tags;

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
		$s = "\n" . $s;

		$this->input = & $s;
		$this->offset = 0;
		$this->output = '';
		$this->tags = array();
		$len = strlen($s);

		while ($this->offset < $len) {
			$matches = $this->{"context$this->context"}();

			if (!$matches) { // EOF
				break;

			} elseif (!empty($matches['comment'])) { // {* *}

			} elseif (!empty($matches['macro'])) { // {macro}
				$code = $this->handler->macro($matches['macro']);
				if ($code === FALSE) {
					throw new ParseException("Unknown macro {{$matches['macro']}}", 0, $this->line);
				}
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

		foreach ($this->tags as $tag) {
			if (!$tag->isMacro && !empty($tag->attrs)) {
				throw new ParseException("Missing end tag </$tag->name> for macro-attribute " . self::HTML_PREFIX
					. implode(' and ' . self::HTML_PREFIX, array_keys($tag->attrs)) . ".", 0, $this->line);
			}
		}

		return $this->output . substr($this->input, $this->offset);
	}



	/**
	 * Handles CONTEXT_TEXT.
	 */
	private function contextText()
	{
		$matches = $this->match('~
			(?:\n[ \t]*)?<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
			<(?P<htmlcomment>!--)|           ##  begin of HTML comment <!--
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['htmlcomment'])) { // <!--
			$this->context = self::CONTEXT_COMMENT;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtmlComment';

		} elseif (empty($matches['closing'])) { // <tag
			$tag = $this->tags[] = (object) NULL;
			$tag->name = $matches['tag'];
			$tag->closing = FALSE;
			$tag->isMacro = Strings::startsWith($tag->name, self::HTML_PREFIX);
			$tag->attrs = array();
			$tag->pos = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';

		} else { // </tag
			do {
				$tag = array_pop($this->tags);
				if (!$tag) {
					//throw new ParseException("End tag for element '$matches[tag]' which is not open.", 0, $this->line);
					$tag = (object) NULL;
					$tag->name = $matches['tag'];
					$tag->isMacro = Strings::startsWith($tag->name, self::HTML_PREFIX);
				}
			} while (strcasecmp($tag->name, $matches['tag']));
			$this->tags[] = $tag;
			$tag->closing = TRUE;
			$tag->pos = strlen($this->output);
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
		$tag = end($this->tags);
		$matches = $this->match('~
			</'.$tag->name.'(?![a-z0-9:])| ##  end HTML tag </tag
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // </tag
			$tag->closing = TRUE;
			$tag->pos = strlen($this->output);
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
			(?P<end>\ ?/?>)(?P<tagnewline>[\ \t]*(?=\r|\n))?|  ##  end of HTML tag
			'.$this->macroRe.'|          ##  curly tag
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## begin of HTML attribute
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['end'])) { // end of HTML tag />
			$tag = end($this->tags);
			$isEmpty = !$tag->closing && (strpos($matches['end'], '/') !== FALSE || isset(Nette\Utils\Html::$emptyElements[strtolower($tag->name)]));

			if ($isEmpty) {
				$matches[0] = (Nette\Utils\Html::$xhtml ? ' />' : '>')
					. (isset($matches['tagnewline']) ? $matches['tagnewline'] : '');
			}

			if ($tag->isMacro || !empty($tag->attrs)) {
				if ($tag->isMacro) {
					$code = $this->handler->tagMacro(substr($tag->name, strlen(self::HTML_PREFIX)), $tag->attrs, $tag->closing);
					if ($code === FALSE) {
						throw new ParseException("Unknown tag-macro <$tag->name>", 0, $this->line);
					}
					if ($isEmpty) {
						$code .= $this->handler->tagMacro(substr($tag->name, strlen(self::HTML_PREFIX)), $tag->attrs, TRUE);
					}
				} else {
					$code = substr($this->output, $tag->pos) . $matches[0] . (isset($matches['tagnewline']) ? "\n" : '');
					$code = $this->handler->attrsMacro($code, $tag->attrs, $tag->closing);
					if ($code === FALSE) {
						throw new ParseException("Unknown macro-attribute " . self::HTML_PREFIX
							. implode(' or ' . self::HTML_PREFIX, array_keys($tag->attrs)), 0, $this->line);
					}
					if ($isEmpty) {
						$code = $this->handler->attrsMacro($code, $tag->attrs, TRUE);
					}
				}
				$this->output = substr_replace($this->output, $code, $tag->pos);
				$matches[0] = ''; // remove from output
			}

			if ($isEmpty) {
				$tag->closing = TRUE;
			}

			if (!$tag->closing && (strcasecmp($tag->name, 'script') === 0 || strcasecmp($tag->name, 'style') === 0)) {
				$this->context = self::CONTEXT_CDATA;
				$this->escape = 'Nette\Templating\DefaultHelpers::escape' . (strcasecmp($tag->name, 'style') ? 'Js' : 'Css');
			} else {
				$this->context = self::CONTEXT_TEXT;
				$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
				if ($tag->closing) array_pop($this->tags);
			}

		} else { // HTML attribute
			$name = $matches['attr'];
			$value = isset($matches['value']) ? $matches['value'] : '';

			// special attribute?
			if ($isSpecial = Strings::startsWith($name, self::HTML_PREFIX)) {
				$name = substr($name, strlen(self::HTML_PREFIX));
			}
			$tag = end($this->tags);
			if ($isSpecial || $tag->isMacro) {
				if ($value === '"' || $value === "'") {
					if ($matches = $this->match('~(.*?)' . $value . '~xsi')) { // overwrites $matches
						$value = $matches[1];
					}
				}
				$tag->attrs[$name] = $value;
				$matches[0] = ''; // remove from output

			} elseif ($value === '"' || $value === "'") { // attribute = "'
				$this->context = self::CONTEXT_ATTRIBUTE;
				$this->quote = $value;
				$this->escape = strncasecmp($name, 'on', 2)
					? ('Nette\Templating\DefaultHelpers::escape' . (strcasecmp($name, 'style') ? 'Html' : 'Css'))
					: 'Nette\Templating\DefaultHelpers::escapeHtmlJs';
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
			$this->escape = 'Nette\Templating\DefaultHelpers::escapeHtml';
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
			(?:\r?\n?)(?P<comment>\\{\\*.*?\\*\\}[\r\n]{0,2})|
			(?P<indent>\n[\ \t]*)?
			' . $left . '
				(?P<macro>(?:' . self::RE_STRING . '|[^\'"]+?)*?)
			' . $right . '
			(?P<newline>[\ \t]*(?=\r|\n))?
		';
		return $this;
	}

}
