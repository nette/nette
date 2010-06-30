<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

namespace Nette;

use Nette;



/**
 * The Nette Framework.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette
 */
final class Framework
{

	/**#@+ Nette Framework version identification */
	const NAME = 'Nette Framework';

	const VERSION = '0.9.6-dev';

	const REVISION = '$WCREV$ released on $WCDATE$';
	/**#@-*/



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Compares current Nette Framework version with given version.
	 * @param  string
	 * @return int
	 */
	public static function compareVersion($version)
	{
		return version_compare($version, self::VERSION);
	}



	/**
	 * Nette Framework promotion.
	 * @return void
	 */
	public static function promo($xhtml = TRUE)
	{
		echo '<a href="http://nette.org/" title="Nette Framework - The Most Innovative PHP Framework"><img ',
			'src="http://files.nette.org/icons/nette-powered.gif" alt="Powered by Nette Framework" width="80" height="15"',
			($xhtml ? ' />' : '>'), '</a>';
	}

}
