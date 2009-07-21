<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2009 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 * @version    $Id$
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../../Object.php';



/**
 * Compile-time filter CurlyBrackets.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
 * @package    Nette\Templates
 */
class CurlyBracketsFilter extends /*Nette\*/Object
{
	/** single & double quoted PHP string */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** PHP identifier */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	/** PHP identifier */
	const RE_CURLY = '
		(?P<indent>\n[ \t]*)?
		\\{(?P<macro>[^\\s\'"{}](?>
			\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"|  # RE_STRING
			[^\'"}]+)*)\\}
		(?P<newline>[\ \t]*(?=\r|\n))?
	';

	/** @var array */
	private $handlers;

	/** @var string */
	public $context, $escape;

	/**#@+ Context-aware escaping states */
	const CONTEXT_TEXT = 1;
	const CONTEXT_CDATA = 2;
	const CONTEXT_TAG = 3;
	const CONTEXT_ATTRIBUTE = 4;
	const CONTEXT_NONE = 5;
	const CONTEXT_COMMENT = 6;
	/**#@-*/



	/**
	 * Adds a new macro handler.
	 * @param  ICurlyBracketsHandler
	 * @return void
	 */
	public function addHandler($handler)
	{
		$this->handlers[] = $handler;
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		// context-aware escaping
		$this->context = CurlyBracketsFilter::CONTEXT_NONE;
		$this->escape = '$template->escape';

		// initialize handlers
		if (empty($this->handlers)) {
			$this->handlers[] = new CurlyBracketsMacros;
		}
		foreach ($this->handlers as $handler) {
			$handler->initialize($this, $s);
		}

		// process all {tags} and <tags/>
		$s = $this->parse("\n" . $s);

		foreach ($this->handlers as $handler) {
			$handler->finalize($s);
		}

		return $s;
	}



	/**
	 * Searches for curly brackets, HTML tags and attributes.
	 * @param  string
	 * @return string
	 */
	private function parse($s)
	{
		$offset = 0;
		$len = strlen($s);
		$output = '';

		while ($offset < $len) {
			switch ($this->context) {
			case self::CONTEXT_TEXT:
				preg_match('~
					<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
					<(?P<comment>!--)|           ##  begin of HTML comment <!--
					'.self::RE_CURLY.'           ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if (!$matches || !empty($matches['macro'][0])) { // EOF or {macro}

				} elseif (!empty($matches['comment'][0])) { // <!--
					$this->context = self::CONTEXT_COMMENT;
					$this->escape = 'TemplateHelpers::escapeHtmlComment';

				} elseif (empty($matches['closing'][0])) { // <tag
					$tagName = $matches['tag'][0];
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';

				} else { // </tag
					$tagName = '';
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_CDATA:
				preg_match('~
					</'.$tagName.'(?![a-z0-9:])| ##  end HTML tag </tag
					'.self::RE_CURLY.'           ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if ($matches && empty($matches['macro'][0])) { // </tag
					$tagName = '';
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_TAG:
				preg_match('~
					(?P<end>>)|                  ##  end of HTML tag
					(?<=\\s)(?P<attr>[a-z0-9:-]+)\s*=\s*(?P<quote>["\'])| ## begin of HTML attribute
					'.self::RE_CURLY.'           ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if (!$matches || !empty($matches['macro'][0])) { // EOF or {macro}

				} elseif (!empty($matches['end'][0])) { // >
					if (strcasecmp($tagName, 'script') === 0 || strcasecmp($tagName, 'style') === 0) {
						$this->context = self::CONTEXT_CDATA;
						$this->escape = strcasecmp($tagName, 'style') ? 'TemplateHelpers::escapeJs' : 'TemplateHelpers::escapeCss';
					} else {
						$this->context = self::CONTEXT_TEXT;
						$this->escape = 'TemplateHelpers::escapeHtml';
					}

				} else { // attribute = '"
						$this->context = self::CONTEXT_ATTRIBUTE;
					$quote = $matches['quote'][0];
					$this->escape = strncasecmp($matches['attr'][0], 'on', 2)
						? (strcasecmp($matches['attr'][0], 'style') ? 'TemplateHelpers::escapeHtml' : 'TemplateHelpers::escapeHtmlCss')
							: 'TemplateHelpers::escapeHtmlJs';
				}
				break;

			case self::CONTEXT_ATTRIBUTE:
				preg_match('~
					(' . $quote . ')|            ##  1) end of HTML attribute
					'.self::RE_CURLY.'           ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if ($matches && empty($matches['macro'][0])) { // (attribute end) '"
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_COMMENT:
				preg_match('~
					(--\s*>)|                    ##  1) end of HTML comment
					'.self::RE_CURLY.'           ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if ($matches && empty($matches['macro'][0])) { // --\s*>
					$this->context = self::CONTEXT_TEXT;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_NONE:
				break 2;
			}


			if (!$matches) { // EOF
				break;
			}

			$output .= substr($s, $offset, $matches[0][1] - $offset); // advance
			$offset = $matches[0][1] + strlen($matches[0][0]);

			if (!empty($matches['macro'][0])) { // {macro|modifiers}
				preg_match('#^(/?[a-z]+)?(.*?)(\\|[a-z](?:'.self::RE_STRING.'|[^\'"\s]+)*)?$()#i', $matches['macro'][0], $m2);
				list(, $macro, $value, $modifiers) = $m2;
				foreach ($this->handlers as $handler) {
					$code = $handler->macro($macro, trim($value), isset($modifiers) ? $modifiers : '');
					if ($code === NULL) continue;
					$nl = isset($matches['newline']) ? "\n" : ''; // double newline
					if ($nl && $matches['indent'][0] && strncmp($code, '<?php echo ', 11)) {
						$output .= "\n" . $code; // remove indent, single newline
					} else {
						$output .= $matches['indent'][0] . $code . $nl;
					}
					continue 2;
				}

				throw new /*\*/InvalidStateException("Unknown macro '{$matches['macro'][0]}'.");

			} else { // common behaviour
				$output .= $matches[0][0];
			}
		}

		return $output . substr($s, $offset);
	}



	/********************* compile-time helpers ****************d*g**/



	/**
	 * Applies modifiers.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function formatModifiers($var, $modifiers)
	{
		if (!$modifiers) return $var;
		preg_match_all(
			'~
				'.self::RE_STRING.'|  ## single or double quoted string
				[^\'"|:,]+|           ## symbol
				[|:,]                 ## separator
			~xs',
			$modifiers . '|',
			$tokens
		);
		$inside = FALSE;
		$prev = '';
		foreach ($tokens[0] as $token) {
			if ($token === '|' || $token === ':' || $token === ',') {
				if ($prev === '') {

				} elseif (!$inside) {
					if (!preg_match('#^'.self::RE_IDENTIFIER.'$#', $prev)) {
						throw new /*\*/InvalidStateException("Modifier name must be alphanumeric string, '$prev' given.");
					}
					$var = "\$template->$prev($var";
					$prev = '';
					$inside = TRUE;

				} else {
					$var .= ', ' . self::formatString($prev);
					$prev = '';
				}

				if ($token === '|' && $inside) {
					$var .= ')';
					$inside = FALSE;
				}
			} else {
				$prev .= $token;
			}
		}
		return $var;
	}



	/**
	 * Reads single token (optionally delimited by comma) from string.
	 * @param  string
	 * @return string
	 */
	public static function fetchToken(& $s)
	{
		if (preg_match('#^((?>'.self::RE_STRING.'|[^\'"\s,]+)+)\s*,?\s*(.*)$#', $s, $matches)) { // token [,] tail
			$s = $matches[2];
			return $matches[1];
		}
		return NULL;
	}



	/**
	 * Formats parameters to PHP array.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function formatArray($s, $prefix = '')
	{
		$s = preg_replace_callback(
			'~
				'.self::RE_STRING.'|                          ## single or double quoted string
				(?<=[,=(]|=>|^)\s*([a-z\d_]+)(?=\s*[,=)]|$)   ## 1) symbol
			~xi',
			array(__CLASS__, 'cbArgs'),
			trim($s)
		);
		return $s === '' ? '' : $prefix . "array($s)";
	}



	/**
	 * Callback for formatArgs().
	 */
	private static function cbArgs($matches)
	{
		//    [1] => symbol

		if (!empty($matches[1])) { // symbol
			list(, $symbol) = $matches;
			static $keywords = array('true'=>1, 'false'=>1, 'null'=>1, 'and'=>1, 'or'=>1, 'xor'=>1, 'clone'=>1, 'new'=>1);
			return is_numeric($symbol) || isset($keywords[strtolower($symbol)]) ? $matches[0] : "'$symbol'";

		} else {
			return $matches[0];
		}
	}



	/**
	 * Formats parameter to PHP string.
	 * @param  string
	 * @return string
	 */
	public static function formatString($s)
	{
		return (is_numeric($s) || strspn($s, '\'"$')) ? $s : '"' . $s . '"';
	}



	/**
	 * Invokes filter.
	 * @deprecated
	 */
	public static function invoke($s)
	{
		$filter = new self;
		return $filter->__invoke($s);
	}

}
