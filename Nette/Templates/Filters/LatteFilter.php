<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

namespace Nette\Templates;

use Nette,
	Nette\String,
	Nette\Tokenizer;



/**
 * Compile-time filter Latte.
 *
 * @author     David Grudl
 */
class LatteFilter extends Nette\Object
{
	/** @internal special HTML tag or attribute prefix */
	const HTML_PREFIX = 'n:';

	/** @var ILatteHandler */
	private $handler;

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
	public $context, $escape;

	/**#@+ @internal Context-aware escaping states */
	const CONTEXT_TEXT = 'text';
	const CONTEXT_CDATA = 'cdata';
	const CONTEXT_TAG = 'tag';
	const CONTEXT_ATTRIBUTE = 'attribute';
	const CONTEXT_NONE = 'none';
	const CONTEXT_COMMENT = 'comment';
	/**#@-*/



	/**
	 * Sets a macro handler.
	 * @param  ILatteHandler
	 * @return LatteFilter  provides a fluent interface
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;
		return $this;
	}



	/**
	 * Returns macro handler.
	 * @return ILatteHandler
	 */
	public function getHandler()
	{
		if ($this->handler === NULL) {
			$this->handler = new LatteMacros;
		}
		return $this->handler;
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		if (!$this->macroRe) {
			$this->setDelimiters('\\{(?![\\s\'"{}])', '\\}');
		}

		// context-aware escaping
		$this->context = LatteFilter::CONTEXT_NONE;
		$this->escape = '$template->escape';

		// initialize handlers
		$this->getHandler()->initialize($this, $s);

		// process all {tags} and <tags/>
		$s = $this->parse("\n" . $s);

		$this->getHandler()->finalize($s);

		return $s;
	}



	/**
	 * Searches for curly brackets, HTML tags and attributes.
	 * @param  string
	 * @return string
	 */
	private function parse($s)
	{
		$this->input = & $s;
		$this->offset = 0;
		$this->output = '';
		$this->tags = array();
		$len = strlen($s);

		while ($this->offset < $len) {
			$matches = $this->{"context$this->context"}();

			if (!$matches) { // EOF
				break;

			} elseif (!empty($matches['macro'])) { // {macro|modifiers}
				list(, $macro, $value, $modifiers) = String::match($matches['macro'], '#^(/?[a-z]+)?(.*?)(\\|[a-z](?:'.Tokenizer::RE_STRING.'|[^\'"]+)*)?$()#is');
				$code = $this->handler->macro($macro, trim($value), isset($modifiers) ? $modifiers : '');
				if ($code === NULL) {
					throw new \InvalidStateException("Unknown macro {{$matches['macro']}} on line $this->line.");
				}
				$nl = isset($matches['newline']) ? "\n" : ''; // double newline
				if ($nl && $matches['indent'] && strncmp($code, '<?php echo ', 11)) {
					$this->output .= "\n" . $code; // remove indent, single newline
				} else {
					$this->output .= $matches['indent'] . $code . (substr($code, -2) === '?>' ? $nl : '');
				}

			} else { // common behaviour
				$this->output .= $matches[0];
			}
		}

		foreach ($this->tags as $tag) {
			if (!$tag->isMacro && !empty($tag->attrs)) {
				throw new \InvalidStateException("Missing end tag </$tag->name> for macro-attribute " . self::HTML_PREFIX . implode(' and ' . self::HTML_PREFIX, array_keys($tag->attrs)) . ".");
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
			<(?P<comment>!--)|           ##  begin of HTML comment <!--
			'.$this->macroRe.'           ##  curly tag
		~xsi');

		if (!$matches || !empty($matches['macro'])) { // EOF or {macro}

		} elseif (!empty($matches['comment'])) { // <!--
			$this->context = self::CONTEXT_COMMENT;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtmlComment';

		} elseif (empty($matches['closing'])) { // <tag
			$tag = $this->tags[] = (object) NULL;
			$tag->name = $matches['tag'];
			$tag->closing = FALSE;
			$tag->isMacro = String::startsWith($tag->name, self::HTML_PREFIX);
			$tag->attrs = array();
			$tag->pos = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';

		} else { // </tag
			do {
				$tag = array_pop($this->tags);
				if (!$tag) {
					//throw new \InvalidStateException("End tag for element '$matches[tag]' which is not open on line $this->line.");
					$tag = (object) NULL;
					$tag->name = $matches['tag'];
					$tag->isMacro = String::startsWith($tag->name, self::HTML_PREFIX);
				}
			} while (strcasecmp($tag->name, $matches['tag']));
			$this->tags[] = $tag;
			$tag->closing = TRUE;
			$tag->pos = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
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

		if ($matches && empty($matches['macro'])) { // </tag
			$tag->closing = TRUE;
			$tag->pos = strlen($this->output);
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
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

		if (!$matches || !empty($matches['macro'])) { // EOF or {macro}

		} elseif (!empty($matches['end'])) { // end of HTML tag />
			$tag = end($this->tags);
			$isEmpty = !$tag->closing && (strpos($matches['end'], '/') !== FALSE || isset(Nette\Web\Html::$emptyElements[strtolower($tag->name)]));

			if ($isEmpty) {
				$matches[0] = (Nette\Web\Html::$xhtml ? ' />' : '>') . (isset($matches['tagnewline']) ? $matches['tagnewline'] : '');
			}

			if ($tag->isMacro || !empty($tag->attrs)) {
				if ($tag->isMacro) {
					$code = $this->handler->tagMacro(substr($tag->name, strlen(self::HTML_PREFIX)), $tag->attrs, $tag->closing);
					if ($code === NULL) {
						throw new \InvalidStateException("Unknown tag-macro <$tag->name> on line $this->line.");
					}
					if ($isEmpty) {
						$code .= $this->handler->tagMacro(substr($tag->name, strlen(self::HTML_PREFIX)), $tag->attrs, TRUE);
					}
				} else {
					$code = substr($this->output, $tag->pos) . $matches[0] . (isset($matches['tagnewline']) ? "\n" : '');
					$code = $this->handler->attrsMacro($code, $tag->attrs, $tag->closing);
					if ($code === NULL) {
						throw new \InvalidStateException("Unknown macro-attribute " . self::HTML_PREFIX . implode(' or ' . self::HTML_PREFIX, array_keys($tag->attrs)) . " on line $this->line.");
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
				$this->escape = strcasecmp($tag->name, 'style') ? 'Nette\Templates\TemplateHelpers::escapeJs' : 'Nette\Templates\TemplateHelpers::escapeCss';
			} else {
				$this->context = self::CONTEXT_TEXT;
				$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
				if ($tag->closing) array_pop($this->tags);
			}

		} else { // HTML attribute
			$name = $matches['attr'];
			$value = empty($matches['value']) ? TRUE : $matches['value'];

			// special attribute?
			if ($isSpecial = String::startsWith($name, self::HTML_PREFIX)) {
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
					? (strcasecmp($name, 'style') ? 'Nette\Templates\TemplateHelpers::escapeHtml' : 'Nette\Templates\TemplateHelpers::escapeHtmlCss')
					: 'Nette\Templates\TemplateHelpers::escapeHtmlJs';
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

		if ($matches && empty($matches['macro'])) { // (attribute end) '"
			$this->context = self::CONTEXT_TAG;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
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

		if ($matches && empty($matches['macro'])) { // --\s*>
			$this->context = self::CONTEXT_TEXT;
			$this->escape = 'Nette\Templates\TemplateHelpers::escapeHtml';
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
		if ($matches = String::match($this->input, $re, PREG_OFFSET_CAPTURE, $this->offset)) {
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
	 * @return LatteFilter  provides a fluent interface
	 */
	public function setDelimiters($left, $right)
	{
		$this->macroRe = '
			(?P<indent>\n[\ \t]*)?
			' . $left . '
				(?P<macro>(?:' . Tokenizer::RE_STRING . '|[^\'"]+?)*?)
			' . $right . '
			(?P<newline>[\ \t]*(?=\r|\n))?
		';
		return $this;
	}



	/**#@+ @deprecated */
	static function formatModifiers($var, $modifiers)
	{
		trigger_error(__METHOD__ . '() is deprecated; use LatteMacros::formatModifiers() instead.', E_USER_WARNING);
		return LatteMacros::formatModifiers($var, $modifiers);
	}

	static function fetchToken(& $s)
	{
		trigger_error(__METHOD__ . '() is deprecated; use LatteMacros::fetchToken() instead.', E_USER_WARNING);
		return LatteMacros::fetchToken($s);
	}

	static function formatArray($input, $prefix = '')
	{
		trigger_error(__METHOD__ . '() is deprecated; use LatteMacros::formatArray() instead.', E_USER_WARNING);
		return LatteMacros::formatArray($input, $prefix);
	}

	static function formatString($s)
	{
		trigger_error(__METHOD__ . '() is deprecated; use LatteMacros::formatString() instead.', E_USER_WARNING);
		return LatteMacros::formatString($s);
	}
	/**#@-*/

}
