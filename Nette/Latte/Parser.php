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

	/** @internal special HTML attribute prefix */
	const N_PREFIX = 'n:';

	/** @var string default macro syntax */
	public $defaultSyntax = 'latte';

	/** @var array */
	public $syntaxes = array(
		'latte' => array('\\{(?![\\s\'"{}])', '\\}'), // {...}
		'double' => array('\\{\\{(?![\\s\'"{}])', '\\}\\}'), // {{...}}
		'asp' => array('<%\s*', '\s*%>'), /* <%...%> */
		'python' => array('\\{[{%]\s*', '\s*[%}]\\}'), // {% ... %} | {{ ... }}
		'off' => array('[^\x00-\xFF]', ''),
	);

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
	private $endTag;

	/** @var bool */
	private $xmlMode;

	/** @internal states */
	const CONTEXT_TEXT = 'text',
		CONTEXT_CDATA = 'cdata',
		CONTEXT_TAG = 'tag',
		CONTEXT_ATTRIBUTE = 'attribute',
		CONTEXT_NONE = 'none',
		CONTEXT_COMMENT = 'comment';



	/**
	 * Process all {macros} and <tags/>.
	 * @param  string
	 * @return array
	 */
	public function parse($input)
	{
		if (substr($input, 0, 3) === "\xEF\xBB\xBF") { // BOM
	    	$input = substr($input, 3);
	    }
		if (!Strings::checkEncoding($input)) {
			throw new Nette\InvalidArgumentException('Template is not valid UTF-8 stream.');
		}
		$input = str_replace("\r\n", "\n", $input);
		$this->input = $input;
		$this->output = array();
		$this->offset = 0;

		$this->setSyntax($this->defaultSyntax);
		$this->setContext(self::CONTEXT_TEXT);
		$this->lastTag = $this->endTag = NULL;
		$this->xmlMode = (bool) preg_match('#^<\?xml\s#m', $input);

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
			<(?P<htmlcomment>!--)|     ##  begin of HTML comment <!--
			'.$this->macroRe.'         ##  macro
		~xsi');

		if (!empty($matches['htmlcomment'])) { // <!--
			$this->addToken(Token::TAG_BEGIN, $matches[0]);
			$this->setContext(self::CONTEXT_COMMENT);

		} elseif (!empty($matches['tag'])) { // <tag or </tag
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
			</(?P<tag>'.$this->lastTag.')(?![a-z0-9:])| ##  end HTML tag </tag
			'.$this->macroRe.'              ##  macro
		~xsi');

		if (!empty($matches['tag'])) { // </tag
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
			'.$this->macroRe.'|          ##  macro
			\s*(?P<attr>[^\s/>={]+)(?:\s*=\s*(?P<value>["\']|[^\s/>{]+))? ## begin of HTML attribute
		~xsi');

		if (!empty($matches['end'])) { // end of HTML tag />
			$this->addToken(Token::TAG_END, $matches[0]);
			$this->setContext(!$this->xmlMode && in_array($this->lastTag, array('script', 'style')) ? self::CONTEXT_CDATA : self::CONTEXT_TEXT);

		} elseif (isset($matches['attr']) && $matches['attr'] !== '') { // HTML attribute
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
			(?P<quote>'.$this->context[1].')|  ##  end of HTML attribute
			'.$this->macroRe.'                 ##  macro
		~xsi');

		if (!empty($matches['quote'])) { // (attribute end) '"
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
			(?P<htmlcomment>--\s*>)|   ##  end of HTML comment
			'.$this->macroRe.'         ##  macro
		~xsi');

		if (!empty($matches['htmlcomment'])) { // --\s*>
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
			'.$this->macroRe.'     ##  macro
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
		$type = $type ?: $this->defaultSyntax;
		if (isset($this->syntaxes[$type])) {
			$this->setDelimiters($this->syntaxes[$type][0], $this->syntaxes[$type][1]);
		} else {
			throw new Nette\InvalidArgumentException("Unknown syntax '$type'");
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
				(?P<noescape>!?)(?P<shortname>/?[=\~#%^&_]?)      ## !expression, !=expression, ...
			)(?P<args>.*?)
			(?P<modifiers>\|[a-z](?:'.Parser::RE_STRING.'|[^\'"])*)?
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
			$this->setSyntax($this->defaultSyntax);
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::MACRO && $token->name === 'syntax') {
			$this->setSyntax($token->value);
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::ATTRIBUTE && $token->name === 'n:syntax') {
			$this->setSyntax($token->value);
			$this->endTag = '/' . $this->lastTag;
			$token->type = Token::COMMENT;

		} elseif ($token->type === Token::TAG_END && $this->lastTag === $this->endTag) {
			$this->setSyntax($this->defaultSyntax);

		} elseif ($token->type === Token::MACRO && $token->name === 'contentType') {
			if (preg_match('#html|xml#', $token->value, $m)) {
				$this->xmlMode = $m[0] === 'xml';
				$this->setContext(self::CONTEXT_TEXT);
			} else {
				$this->setContext(self::CONTEXT_NONE);
			}
		}
	}

}
