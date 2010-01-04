<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette\Templates
 */

/*namespace Nette\Templates;*/



/**
 * Old macros for filter LatteFilter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Templates
 * @deprecated
 */
class OldLatteMacros extends LatteMacros
{
	/** @var array */
	public static $defaultMacros = array(
		'snippet' => '<?php } if ($_cb->foo = SnippetHelper::create($control%:macroOldSnippet%)) { $_cb->snippets[] = $_cb->foo ?>',
		'/snippet' => '<?php array_pop($_cb->snippets)->finish(); } if (SnippetHelper::$outputAllowed) { ?>',
		'widget' => '<?php %:macroWidget% ?>',
	);



	/**
	 * {snippet ...}
	 */
	public function macroOldSnippet($content)
	{
		$args = array('');
		if ($snippet = LatteFilter::fetchToken($content)) {  // [name [,]] [tag]
			$args[] = LatteFilter::formatString($snippet);
		}
		if ($content) {
			$args[] = LatteFilter::formatString($content);
		}
		return implode(', ', $args);
	}

}



class CurlyBracketsFilter extends LatteFilter {}
class CurlyBracketsMacros extends OldLatteMacros {}
