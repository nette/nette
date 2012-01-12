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
 * Latte parser.
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

	/** @var array of Token */
	private $output;

	/** @var int  position on source template */
	private $offset;

	/** @var array */
	private $context;

	/** @var string */
	private $lastTag;

	/** @var string used by filter() */
	public $endTag;

	/** @internal states */
	const CONTEXT_TEXT = 'text',
		CONTEXT_CDATA = 'cdata',
		CONTEXT_TAG = 'tag',
		CONTEXT_ATTRIBUTE = 'attribute',
		CONTEXT_NONE = 'none',
		CONTEXT_COMMENT = 'comment';


	public function __construct()
	{
		$this->setSyntax('latte');
		$this->setContext(self::CONTEXT_TEXT);
	}



	/**
	 * Process all {macros} and <tags/>.
	 * @param  string
	 * @return array
	 */
	public function parse($input)
	{
		if (!Strings::checkEncoding($input)) {
			throw new ParseException('Template is not valid UTF-8 stream.');
		}
		$input = str_replace("\r\n", "\n", $input);

		$this->input = $input;
		$this->output = array();
		$this->offset = 0;

		while ($this->offset < strlen($input)) {
			$matches = $this->{"context".$this->context[0]}();

			if (!$matches) { // EOF
				break;

			} elseif (!empty($matches['comment'])) { // {* *}
				$this->addToken(Token::COMMENT, $matches[0]);

			} elseif (!empty($matches['macro'])) { // {macro}
				$token = $this->addToken(Token::MACRO, $matches[0]);
				list($token->name, $token->value, $token->modifiers) = $this->parseMacro($matches['macro']);
			}

			$this->filter();
		}

		if ($this->offset < strlen($input)) {
			$this->addToken(Token::TEXT, substr($this->input, $this->offset));
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
			<(?P<htmlcomment>!--)|       ##  begin of HTML comment <!--
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['htmlcomment'])) { // <!--
			$this->addToken(Token::TAG_BEGIN, $matches[0]);
			$this->setContext(self::CONTEXT_COMMENT);

		} else { // <tag or </tag
			$token = $this->addToken(Token::TAG_BEGIN, $matches[0]);
			$token->name = $matches['tag'];
			$token->closing = (bool) $matches['closing'];
			$this->lastTag = $matches['closing'] . strtolower($matches['tag']);
			$this->setContext(self::CONTEXT_TAG);
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_CDATA.
	 */
	private function contextCData()
	{
		$matches = $this->match('~
			</'.$this->lastTag.'(?![a-z0-9:])| ##  end HTML tag </tag
			'.$this->macroRe.'              ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // </tag
			$token = $this->addToken(Token::TAG_BEGIN, $matches[0]);
			$token->name = $this->lastTag;
			$token->closing = TRUE;
			$this->lastTag = '/' . $this->lastTag;
			$this->setContext(self::CONTEXT_TAG);
		}
		return $matches;
	}



	/**
	 * Handles CONTEXT_TAG.
	 */
	private function contextTag()
	{
		$matches = $this->match('~
			(?P<end>\ ?/?>)([ \t]*\n)?|  ##  end of HTML tag
			'.$this->macroRe.'|          ##  curly tag
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## begin of HTML attribute
		~xsi');

		if (!$matches || !empty($matches['macro']) || !empty($matches['comment'])) { // EOF or {macro}

		} elseif (!empty($matches['end'])) { // end of HTML tag />
			$this->addToken(Token::TAG_END, $matches[0]);
			$this->setContext($this->lastTag === 'script' || $this->lastTag === 'style' ? self::CONTEXT_CDATA : self::CONTEXT_TEXT);

		} else { // HTML attribute
			$token = $this->addToken(Token::ATTRIBUTE, $matches[0]);
			$token->name = $matches['attr'];
			$token->value = isset($matches['value']) ? $matches['value'] : '';

			if ($token->value === '"' || $token->value === "'") { // attribute = "'
				if (Strings::startsWith($token->name, self::N_PREFIX)) {
					$token->value = '';
					if ($m = $this->match('~(.*?)' . $matches['value'] . '~xsi')) {
						$token->value = $m[1];
						$token->text .= $m[0];
					}
				} else {
					$this->setContext(self::CONTEXT_ATTRIBUTE, $matches['value']);
				}
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
			('.$this->context[1].')|      ##  1) end of HTML attribute
			'.$this->macroRe.'            ##  curly tag
		~xsi');

		if ($matches && empty($matches['macro']) && empty($matches['comment'])) { // (attribute end) '"
			$this->addToken(Token::TEXT, $matches[0]);
			$this->setContext(self::CONTEXT_TAG);
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
			$this->addToken(Token::TAG_END, $matches[0]);
			$this->setContext(self::CONTEXT_TEXT);
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
			$value = substr($this->input, $this->offset, $matches[0][1] - $this->offset);
			if ($value !== '') {
				$this->addToken(Token::TEXT, $value);
			}
			$this->offset = $matches[0][1] + strlen($matches[0][0]);
			foreach ($matches as $k => $v) $matches[$k] = $v[0];
		}
		return $matches;
	}



	/**
	 * @return Parser  provides a fluent interface
	 */
	public function setContext($context, $quote = NULL)
	{
		$this->context = array($context, $quote);
		return $this;
	}



	/**
	 * Changes macro delimiters.
	 * @param  string
	 * @return Parser  provides a fluent interface
	 */
	public function setSyntax($type)
	{
		if ($type === '' || $type === 'latte') {
			$this->setDelimiters('\\{(?![\\s\'"{}])', '\\}'); // {...}

		} elseif ($type === 'double') {
			$this->setDelimiters('\\{\\{(?![\\s\'"{}])', '\\}\\}'); // {{...}}

		} elseif ($type === 'asp') {
			$this->setDelimiters('<%\s*', '\s*%>'); /* <%...%> */

		} elseif ($type === 'python') {
			$this->setDelimiters('\\{[{%]\s*', '\s*[%}]\\}'); // {% ... %} | {{ ... }}

		} elseif ($type === 'off') {
			$this->setDelimiters('[^\x00-\xFF]', '');

		} else {
			throw new ParseException("Unknown syntax '$type'");
		}
		return $this;
	}



	/**
	 * Changes macro delimiters.
	 * @param  string  left regular expression
	 * @param  string  right regular expression
	 * @return Parser  provides a fluent interface
	 */
	public function setDelimiters($left, $right)
	{
		$this->macroRe = '
			(?P<comment>' . $left . '\\*.*?\\*' . $right . '\n{0,2})|
			' . $left . '
				(?P<macro>(?:' . self::RE_STRING . '|\{
						(?P<inner>' . self::RE_STRING . '|\{(?P>inner)\}|[^\'"{}])*+
				\}|[^\'"{}])+?)
			' . $right . '
			(?P<rmargin>[ \t]*(?=\n))?
		';
		return $this;
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



	private function addToken($type, $text)
	{
		$this->output[] = $token = new Token;
		$token->type = $type;
		$token->text = $text;
		$token->line = substr_count($this->input, "\n", 0, max(1, $this->offset - 1)) + 1;
		return $token;
	}



	/**
	 * Process low-level macros.
	 */
	protected function filter()
	{
		$token = end($this->output);
		if ($token->type === Token::MACRO && $token->name === '/syntax') {
			$this->setSyntax('latte');

		} elseif ($token->type === Token::MACRO && $token->name === 'syntax') {
			$this->setSyntax($token->value);

		} elseif ($token->type === Token::ATTRIBUTE && $token->name === 'n:syntax') {
			$this->setSyntax($token->value);
			$this->endTag = '/' . $this->lastTag;

		} elseif ($token->type === Token::TAG_END && $this->lastTag === $this->endTag) {
			$this->setSyntax('latte');

		} elseif ($token->type === Token::MACRO && $token->name === 'contentType') {
			$this->setContext(Strings::contains($token->value, 'html') ? self::CONTEXT_TEXT : self::CONTEXT_NONE);
		}
	}

}
