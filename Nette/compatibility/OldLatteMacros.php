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
 */

/*namespace Nette\Templates;*/



require_once dirname(__FILE__) . '/../Templates/Filters/LatteMacros.php';



/**
 * Old macros for filter LatteFilter.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2009 David Grudl
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
