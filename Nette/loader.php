<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt, and/or GPL license.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 */



/**
 * Check PHP configuration.
 */
/**/
if (version_compare(PHP_VERSION, '5.2.0', '<')) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}
/**/

@set_magic_quotes_runtime(FALSE); // intentionally @



/**
 * Nette\Callback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return Nette\Callback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof /*Nette\*/Callback) ? $callback : new /*Nette\*/Callback($callback, $m);
}



/**
 * Nette\Debug::dump shortcut.
 */
if (!function_exists('dump')) {
	function dump($var)
	{
		foreach (func_get_args() as $arg) /*Nette\*/Debug::dump($arg);
		return $var;
	}
}



require_once dirname(__FILE__) . '/exceptions.php';
require_once dirname(__FILE__) . '/Framework.php';
require_once dirname(__FILE__) . '/Object.php';
require_once dirname(__FILE__) . '/ObjectMixin.php';
require_once dirname(__FILE__) . '/Callback.php';
require_once dirname(__FILE__) . '/Loaders/LimitedScope.php';
require_once dirname(__FILE__) . '/Loaders/AutoLoader.php';
require_once dirname(__FILE__) . '/Loaders/NetteLoader.php';


/*Nette\Loaders\*/NetteLoader::getInstance()->base = dirname(__FILE__);
/*Nette\Loaders\*/NetteLoader::getInstance()->register();
