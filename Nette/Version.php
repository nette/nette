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
 * Software version storage.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette
 */
final class Version
{

    /**
     * Nette Framework version identification.
     */
	const VERSION = '0.7';

	const REVISION = '$WCREV$ released on $WCDATE$';



    /**
     * Compares current Nette Framework version with given version.
     * @param  string
     * @return int
     */
    public static function compare($version)
    {
        return version_compare($version, self::VERSION);
    }

}
