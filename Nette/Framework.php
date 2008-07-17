<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

/*namespace Nette;*/



/**
 * The Nette Framework.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
final class Framework
{
    /**
     * Nette Framework version identification.
     */
	const VERSION = '0.7';

	const REVISION = '$WCREV$ released on $WCDATE$';



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new /*::*/LogicException("Cannot instantiate static class " . get_class($this));
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
		echo '<a href="http://nettephp.com/" title="Nette Framework - The Most Innovative PHP Framework"><img ',
			'src="http://nettephp.com/images/nette-powered.gif" alt="Powered by Nette Framework" width="80" height="15"',
			($xhtml ? ' />' : '>'), '</a>';
	}

}
