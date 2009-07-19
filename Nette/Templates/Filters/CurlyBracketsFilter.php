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
 * Template compile-time filter curlyBrackets supports for {...} in template.
 *
 * - {$variable} with escaping
 * - {!$variable} without escaping
 * - {*comment*} will be removed
 * - {=expression} echo with escaping
 * - {!=expression} echo without escaping
 * - {?expression} evaluate PHP statement
 * - {_expression} echo translation with escaping
 * - {!_expression} echo translation without escaping
 * - {link destination ...} control link
 * - {plink destination ...} presenter link
 * - {if ?} ... {elseif ?} ... {else} ... {/if} // or <%else%>, <%/if%>, <%/foreach%> ?
 * - {for ?} ... {/for}
 * - {foreach ?} ... {/foreach}
 * - {include ?}
 * - {cache ?} ... {/cache} cached block
 * - {snippet ?} ... {/snippet ?} control snippet
 * - {attr ?} HTML element attributes
 * - {block|texy} ... {/block} capture of filter block
 * - {contentType ...} HTTP Content-Type header
 * - {assign $var value} set template parameter
 * - {dump $var}
 * - {debugbreak}
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

	/** @var array */
	public static $defaultMacros = array(
		'block' => '<?php %:macroBlock% ?>',
		'/block' => '<?php %:macroBlockEnd% ?>',

		'snippet' => '<?php } if ($_cb->foo = SnippetHelper::create($control%:macroSnippet%)) { $_cb->snippets[] = $_cb->foo; ?>',
		'/snippet' => '<?php array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) { ?>',

		'cache' => '<?php if ($_cb->foo = CachingHelper::create($_cb->key = md5(__FILE__) . __LINE__, $template->getFile(), array(%%))) { $_cb->caches[] = $_cb->foo; ?>',
		'/cache' => '<?php array_pop($_cb->caches)->save(); } if (!empty($_cb->caches)) end($_cb->caches)->addItem($_cb->key); ?>',

		'if' => '<?php if (%%): ?>',
		'elseif' => '<?php elseif (%%): ?>',
		'else' => '<?php else: ?>',
		'/if' => '<?php endif ?>',
		'foreach' => '<?php foreach (%:macroForeach%): ?>',
		'/foreach' => '<?php endforeach; array_pop($_cb->its); $iterator = end($_cb->its) ?>',
		'for' => '<?php for (%%): ?>',
		'/for' => '<?php endfor ?>',
		'while' => '<?php while (%%): ?>',
		'/while' => '<?php endwhile ?>',
		'continueIf' => '<?php if (%%) continue ?>',
		'breakIf' => '<?php if (%%) break ?>',

		'include' => '<?php %:macroInclude% ?>',
		'extends' => '<?php %:macroExtends% ?>',

		'plink' => '<?php echo %:macroEscape%(%:macroPlink%) ?>',
		'link' => '<?php echo %:macroEscape%(%:macroLink%) ?>',
		'ifCurrent' => '<?php %:macroIfCurrent%; if ($presenter->getLastCreatedRequestFlag("current")): ?>',
		'widget' => '<?php %:macroWidget% ?>',

		'attr' => '<?php echo Html::el(NULL)->%:macroAttr%attributes() ?>',
		'contentType' => '<?php %:macroContentType% ?>',
		'assign' => '<?php %:macroAssign% ?>', // deprecated?
		'dump' => '<?php Debug::consoleDump(%:macroDump%, "Template " . str_replace(Environment::getVariable("templatesDir"), "\xE2\x80\xA6", $template->getFile())) ?>',
		'debugbreak' => '<?php if (function_exists("debugbreak")) debugbreak() ?>',

		'!_' => '<?php echo $template->translate(%:formatModifiers%) ?>',
		'!=' => '<?php echo %:formatModifiers% ?>',
		'_' => '<?php echo %:macroEscape%($template->translate(%:formatModifiers%)) ?>',
		'=' => '<?php echo %:macroEscape%(%:formatModifiers%) ?>',
		'!$' => '<?php echo %:macroVar% ?>',
		'!' => '<?php echo %:macroVar% ?>', // deprecated
		'$' => '<?php echo %:macroEscape%(%:macroVar%) ?>',
		'?' => '<?php %:formatModifiers% ?>', // deprecated?
	);

	/** @var array */
	public $macros;

	/** @var array */
	private $blocks = array();

	/** @var array */
	private $namedBlocks = array();

	/** @var bool */
	private $extends;

	/** @var string */
	private $context, $escape;

	/**#@+ Context-aware escaping states */
	const CONTEXT_TEXT = 1;
	const CONTEXT_CDATA = 2;
	const CONTEXT_TAG = 3;
	const CONTEXT_ATTRIBUTE = 4;
	const CONTEXT_NONE = 5;
	const CONTEXT_COMMENT = 6;
	/**#@-*/



	/**
	 * Invokes filter.
	 * @deprecated
	 */
	public static function invoke($s)
	{
		$filter = new self;
		return $filter->__invoke($s);
	}



	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->macros = self::$defaultMacros;
	}



	/**
	 * Invokes filter.
	 * @param  string
	 * @return string
	 */
	public function __invoke($s)
	{
		$this->blocks = array();
		$this->namedBlocks = array();
		$this->extends = NULL;

		// context-aware escaping
		$this->context = self::CONTEXT_TEXT;
		$this->escape = 'TemplateHelpers::escapeHtml';

		// remove comments
		$s = preg_replace('#\\{\\*.*?\\*\\}[\r\n]*#s', '', $s);

		// snippets support (temporary solution)
		$s = preg_replace(
			'#@(\\{[^}]+?\\})#s',
			'<?php } ?>$1<?php if (SnippetHelper::\\$outputAllowed) { ?>',
			$s
		);

		// process all {tags} and <tags/>
		$s = $this->parse("\n" . $s);

		// blocks closing check
		if (count($this->blocks) === 1) { // auto-close last block
			$s .= $this->macro('/block', '', '');

		} elseif ($this->blocks) {
			throw new /*\*/InvalidStateException("There are some unclosed blocks.");
		}

		// snippets support (temporary solution)
		$s = "<?php\nif (SnippetHelper::\$outputAllowed) {\n?>$s<?php\n}\n?>";

		// extends support
		if ($this->namedBlocks || $this->extends) {
			$s = "<?php\n"
				. 'if ($_cb->extends) { ob_start(); }' . "\n"
				. '?>' . $s . "<?php\n"
				. 'if ($_cb->extends) { ob_end_clean(); CurlyBracketsFilter::includeTemplate($_cb->extends, get_defined_vars(), $template)->render(); }' . "\n";
		}

		// named blocks
		if ($this->namedBlocks) {
			foreach (array_reverse($this->namedBlocks, TRUE) as $name => $foo) {
				$name = preg_quote($name, '#');
				$s = preg_replace_callback("#{block($name)} \?>(.*)<\?php {/block$name}#sU", array($this, 'cbNamedBlocks'), $s);
			}
			$s = "<?php\n\n" . implode("\n\n\n", $this->namedBlocks) . "\n\n//\n// end of blocks\n//\n?>" . $s;
		}

		// internal state holder
		$s = "<?php\n"
			/*. 'use Nette\Templates\CurlyBracketsFilter, Nette\Templates\TemplateHelpers, Nette\SmartCachingIterator, Nette\Web\Html, Nette\Templates\SnippetHelper, Nette\Debug, Nette\Environment, Nette\Templates\CachingHelper;' . "\n\n"*/
			. "\$_cb = CurlyBracketsFilter::initRuntime(\$template, " . var_export($this->extends, TRUE) . ", __FILE__); unset(\$_extends);\n"
			. '?>' . $s;

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
		$output = $tagName = '';
		$curlyRE = '
			(?P<indent>\n[ \t]*)?
			\\{(?P<macro>[^\\s\'"{}](?>'.self::RE_STRING.'|[^\'"}]+)*)\\}
			(?P<newline>[\ \t]*(?=\r|\n))?
		';

		while ($offset < $len) {
			switch ($this->context) {
			case self::CONTEXT_TEXT:
				preg_match('~
					<(?P<closing>/?)(?P<tag>[a-z0-9:]+)|  ##  begin of HTML tag <tag </tag - ignores <!DOCTYPE
					<(?P<comment>!--)|         ##  begin of HTML comment <!--
					'.$curlyRE.'               ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if (!$matches || !empty($matches['macro'][0])) { // EOF or {macro}

				} elseif (!empty($matches['comment'][0])) { // <!--
					$this->context = self::CONTEXT_COMMENT;
					$this->escape = 'TemplateHelpers::escapeHtmlComment';

				} elseif (empty($matches['closing'][0])) { // <tag
					$tagName = strtolower($matches['tag'][0]);
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
					'.$curlyRE.'                 ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if ($matches && empty($matches['macro'][0])) { // </tag
					$tagName = '';
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_TAG:
				preg_match('~
					(?P<end>>)|                ##  end of HTML tag
					(?<=\\s)(?P<attr>[a-z0-9:-]+)\s*=\s*(?P<quote>["\'])| ## begin of HTML attribute
					'.$curlyRE.'               ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if (!$matches || !empty($matches['macro'][0])) { // EOF or {macro}

				} elseif (!empty($matches['end'][0])) { // >
					if ($tagName === 'script' || $tagName === 'style') {
						$this->context = self::CONTEXT_CDATA;
						$this->escape = $tagName === 'script' ? 'TemplateHelpers::escapeJs' : 'TemplateHelpers::escapeCss';
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
					(' . $quote . ')|  ##  1) end of HTML attribute
					'.$curlyRE.'               ##  curly tag
				~xsi', $s, $matches, PREG_OFFSET_CAPTURE, $offset);

				if ($matches && empty($matches['macro'][0])) { // (attribute end) '"
					$this->context = self::CONTEXT_TAG;
					$this->escape = 'TemplateHelpers::escapeHtml';
				}
				break;

			case self::CONTEXT_COMMENT:
				preg_match('~
					(--\s*>)|                  ##  1) end of HTML comment
					'.$curlyRE.'               ##  curly tag
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
				if (preg_match('#^(.*?)(\\|[a-z](?:'.self::RE_STRING.'|[^\'"\s]+)*)$#i', $matches['macro'][0], $tmp)) {
					list(, $macro, $modifiers) = $tmp;
				} else {
					$macro = $matches['macro'][0];
					$modifiers = NULL;
				}

				foreach ($this->macros as $key => $val) {
					if (strncmp($macro, $key, strlen($key))) {
						continue;
					}
					$macro = substr($macro, strlen($key));
					if (preg_match('#[a-zA-Z0-9]$#', $key) && preg_match('#^[a-zA-Z0-9._-]#', $macro)) {
						continue;
					}
					$macro = $this->macro($key, trim($macro), isset($modifiers) ? $modifiers : '');
					$nl = isset($matches['newline']) ? "\n" : ''; // double newline
					if ($nl && $matches['indent'][0] && strncmp($macro, '<?php echo ', 11)) {
						$output .= "\n" . $macro; // remove indent, single newline
					} else {
						$output .= $matches['indent'][0] . $macro . $nl;
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



	/**
	 * Process specified macro.
	 */
	public function macro($macro, $var, $modifiers)
	{
		$this->_cbMacro = array($var, $modifiers);
		return preg_replace_callback('#%(.*?)%#', array($this, 'cbMacro'), $this->macros[$macro]);
	}



	/** @var array */
	private $_cbMacro;

	/**
	 * Callback for self::macro().
	 */
	private function cbMacro($m)
	{
		list($var, $modifiers) = $this->_cbMacro;
		if ($m[1]) {
			$callback = $m[1][0] === ':' ? array($this, substr($m[1], 1)) : $m[1];
			/**/fixCallback($callback);/**/
			if (!is_callable($callback)) {
				$able = is_callable($callback, TRUE, $textual);
				throw new /*\*/InvalidStateException("CurlyBrackets macro handler '$textual' is not " . ($able ? 'callable.' : 'valid PHP callback.'));
			}
			return call_user_func($callback, $var, $modifiers);

		} else {
			return $var;
		}
	}



	/**
	 * {$var |modifiers}
	 */
	private function macroVar($var, $modifiers)
	{
		return $this->formatModifiers('$' . $var, $modifiers);
	}



	/**
	 * {include ...}
	 */
	private function macroInclude($var, $modifiers)
	{
		$destination = $this->fetchToken($var); // destination [,] [params]
		$params = $this->formatArray($var) . ($var ? ' + ' : '');

		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {include}.");

		} elseif ($destination[0] === '#') { // include #block
			if (!preg_match('#^\\#'.self::RE_IDENTIFIER.'$#', $destination)) {
				throw new /*\*/InvalidStateException("Included block name must be alphanumeric string, '$destination' given.");
			}

			$parent = $destination === '#parent';
			if ($destination === '#parent' || $destination === '#this') {
				$item = end($this->blocks);
				while ($item && $item[0][0] !== '#') $item = prev($this->blocks);
				if (!$item) {
					throw new /*\*/InvalidStateException("Cannot include $name block outside of any block.");
				}
				$destination = $item[0];
			}
			$name = var_export($destination, TRUE);
			$params .= 'get_defined_vars()';
			$cmd = isset($this->namedBlocks[$destination]) && !$parent
				? "call_user_func(reset(\$_cb->blocks[$name]), $params)"
				: "CurlyBracketsFilter::callBlock" . ($parent ? 'Parent' : '') . "(\$_cb->blocks, $name, $params)";
			return $modifiers
				? "ob_start(); $cmd; echo " . $this->formatModifiers('ob_get_clean()', $modifiers)
				: $cmd;

		} else { // include "file"
			$destination = $this->formatString($destination);
			$params .= '$template->getParams()';
			return $modifiers
				? 'echo ' . $this->formatModifiers('CurlyBracketsFilter::includeTemplate(' . $destination . ', ' . $params . ', $_cb->templates[__FILE__])->__toString(TRUE)', $modifiers)
				: 'CurlyBracketsFilter::includeTemplate(' . $destination . ', ' . $params . ', $_cb->templates[__FILE__])->render()';
		}
	}



	/**
	 * {extends ...}
	 */
	private function macroExtends($var)
	{
		$destination = $this->fetchToken($var); // destination
		if ($destination === NULL) {
			throw new /*\*/InvalidStateException("Missing destination in {extends}.");
		}
		if (!empty($this->blocks)) {
			throw new /*\*/InvalidStateException("{extends} must be placed outside any block.");
		}
		if ($this->extends !== NULL) {
			throw new /*\*/InvalidStateException("Multiple {extends} declarations are not allowed.");
		}
		$this->extends = $destination !== 'none';
		return $this->extends ? '$_cb->extends = ' . $this->formatString($destination) : '';
	}



	/**
	 * {block ...}
	 */
	private function macroBlock($var, $modifiers)
	{
		$name = $this->fetchToken($var); // block [,] [params]

		if ($name === NULL || $name[0] === '$') { // anonymous block or capture
			$this->blocks[] = array($name, $modifiers);
			return ($name === NULL && $modifiers === '') ? '' : 'ob_start()';

		} elseif ($name[0] === '#') { // #block
			if (!preg_match('#^\\#'.self::RE_IDENTIFIER.'$#', $name)) {
				throw new /*\*/InvalidStateException("Block name must be alphanumeric string, '$name' given.");

			} elseif (isset($this->namedBlocks[$name])) {
				throw new /*\*/InvalidStateException("Cannot redeclare block '$name'.");
			}

			$top = empty($this->blocks);
			$this->namedBlocks[$name] = $name;
			$this->blocks[] = array($name, '');
			if (!$top) {
				return $this->macroInclude($name, $modifiers) . "{block$name}";

			} elseif ($this->extends) {
				return "{block$name}";

			} else {
				return 'if (!$_cb->extends) { ' . $this->macroInclude($name, $modifiers) . "; } {block$name}";
			}

		} else {
			throw new /*\*/InvalidStateException("Invalid block parameter '$name'.");
		}
	}



	/**
	 * {/block ...}
	 */
	private function macroBlockEnd($var)
	{
		$empty = empty($this->blocks);
		list($name, $modifiers) = array_pop($this->blocks);

		if ($empty || ($var && $var !== $name)) {
			throw new /*\*/InvalidStateException("Tag {/block $var} was not expected here.");

		} elseif (substr($name, 0, 1) === '#') { // #block
			return "{/block$name}";

		} else { // anonymous block or capture
			return ($name === NULL && $modifiers === '') ? ''
				: ($name === NULL ? 'echo ' : $name . '=') . $this->formatModifiers('ob_get_clean()', $modifiers);
		}
	}



	/**
	 * Converts {block#named}...{/block} to functions.
	 */
	private function cbNamedBlocks($matches)
	{
		list(, $name, $content) = $matches;
		$func = '_cbb' . substr(md5(uniqid($name)), 0, 10) . '_' . preg_replace('#[^a-z0-9_]#i', '_', $name);
		$this->namedBlocks[$name] = "//\n// block $name\n//\n"
			. "if (!function_exists(\$_cb->blocks[" . var_export($name, TRUE) . "][] = '$func')) { function $func() { extract(func_get_arg(0))\n?>$content<?php\n}}";
		return '';
	}



	/**
	 * {foreach ...}
	 */
	private function macroForeach($var)
	{
		return '$iterator = $_cb->its[] = new SmartCachingIterator(' . preg_replace('# +as +#i', ') as ', $var, 1);
	}



	/**
	 * {attr ...}
	 */
	private function macroAttr($var)
	{
		return str_replace(') ', ')->', $var . ' ');
	}



	/**
	 * {contentType ...}
	 */
	private function macroContentType($var)
	{
		if (strpos($var, 'html') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeHtml';
			$this->context = self::CONTEXT_TEXT;

		} elseif (strpos($var, 'xml') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeXml';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'javascript') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeJs';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'css') !== FALSE) {
			$this->escape = 'TemplateHelpers::escapeCss';
			$this->context = self::CONTEXT_NONE;

		} elseif (strpos($var, 'plain') !== FALSE) {
			$this->escape = '';
			$this->context = self::CONTEXT_NONE;

		} else {
			$this->escape = '$template->escape';
			$this->context = self::CONTEXT_NONE;
		}

		// temporary solution
		return strpos($var, '/') ? /*\Nette\*/'Environment::getHttpResponse()->setHeader("Content-Type", "' . $var . '")' : '';
	}



	/**
	 * {dump ...}
	 */
	private function macroDump($var)
	{
		return $var ? "array('$var' => $var)" : 'get_defined_vars()';
	}



	/**
	 * {snippet ...}
	 */
	private function macroSnippet($var)
	{
		$args = array('');
		if ($snippet = $this->fetchToken($var)) {  // [name [,]] [tag]
			$args[] = $this->formatString($snippet);
		}
		if ($var) {
			$args[] = $this->formatString($var);
		}
		return implode(', ', $args);
	}



	/**
	 * {widget ...}
	 */
	private function macroWidget($var, $modifiers)
	{
		// TODO: add support for $modifiers
		// TODO: check arguments
		$pair = explode(':', $this->fetchToken($var), 2);
		$method = isset($pair[1]) ? ucfirst($pair[1]) : '';
		$method = preg_match('#^'.self::RE_IDENTIFIER.'|$#', $method) ? "render$method" : "{\"render$method\"}";
		return "\$control->getWidget(\"$pair[0]\")->$method({$this->formatArray($var)})";
	}



	/**
	 * {link ...}
	 */
	private function macroLink($var, $modifiers)
	{
		return $this->formatModifiers('$control->link(' . $this->formatLink($var) .')', $modifiers);
	}



	/**
	 * {plink ...}
	 */
	private function macroPlink($var, $modifiers)
	{
		return $this->formatModifiers('$presenter->link(' . $this->formatLink($var) .')', $modifiers);
	}



	/**
	 * {ifCurrent ...}
	 */
	private function macroIfCurrent($var, $modifiers)
	{
		return $var ? $this->formatModifiers('$presenter->link(' . $this->formatLink($var) .')', $modifiers) : '';
	}



	/**
	 * Formats {*link ...} parameters.
	 */
	private function formatLink($var)
	{
		return $this->formatString($this->fetchToken($var)) . $this->formatArray($var, ', '); // destination [,] args
	}



	/**
	 * {assign ...}
	 */
	private function macroAssign($var, $modifiers)
	{
		$param = ltrim($this->fetchToken($var), '$'); // [$]params value
		return '$' . $param . ' = ' . $this->formatModifiers($var === '' ? 'NULL' : $var, $modifiers);
	}



	/**
	 * Escaping helper.
	 */
	private function macroEscape($var)
	{
		return $this->escape;
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



	/********************* run-time helpers ****************d*g**/



	/**
	 * Calls block.
	 * @param  array
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlock(& $blocks, $name, $params)
	{
		if (empty($blocks[$name])) {
			throw new /*\*/InvalidStateException("Call to undefined block '$name'.");
		}
		$block = reset($blocks[$name]);
		$block($params);
	}



	/**
	 * Calls parent block.
	 * @param  array
	 * @param  string
	 * @param  array
	 * @return void
	 */
	public static function callBlockParent(& $blocks, $name, $params)
	{
		if (empty($blocks[$name]) || ($block = next($blocks[$name])) === FALSE) {
			throw new /*\*/InvalidStateException("Call to undefined parent block '$name'.");
		}
		$block($params);
	}



	/**
	 * Includes subtemplate.
	 * @param  mixed      included file name or template
	 * @param  array      parameters
	 * @param  ITemplate  current template
	 * @return Template
	 */
	public static function includeTemplate($destination, $params, $template)
	{
		if ($destination instanceof ITemplate) {
			$tpl = $destination;

		} elseif ($destination == NULL) { // intentionally ==
			throw new /*\*/InvalidArgumentException("Template file name was not specified.");

		} else {
			$tpl = clone $template;
			if ($template instanceof IFileTemplate) {
				if (substr($destination, 0, 1) !== '/' && substr($destination, 1, 1) !== ':') {
					$destination = dirname($template->getFile()) . '/' . $destination;
				}
				$tpl->setFile($destination);
			}
		}

		$tpl->setParams($params); // interface?
		return $tpl;
	}



	/**
	 * Initializes state holder $_cb in template.
	 * @param  ITemplate
	 * @param  bool
	 * @param  string
	 * @return stdClass
	 */
	public static function initRuntime($template, $extends, $realFile)
	{
		$cb = (object) NULL;

		// extends support
		if (isset($template->_cb)) {
			$cb->blocks = & $template->_cb->blocks;
			$cb->templates = & $template->_cb->templates;
		}
		$cb->templates[$realFile] = $template;
		$cb->extends = is_bool($extends) ? $extends : (empty($template->_extends) ? FALSE : $template->_extends);
		unset($template->_cb, $template->_extends);

		// cache support
		if (!empty($cb->caches)) {
			end($cb->caches)->addFile($template->getFile());
		}

		return $cb;
	}

}
