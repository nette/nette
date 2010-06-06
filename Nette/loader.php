<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt, and/or GPL license.
 *
 * For more information please see http://nette.org
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */



/**
 * Check PHP configuration.
 */
if (!defined('PHP_VERSION_ID')) {
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

/*5.2*
if (PHP_VERSION_ID < 50200) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}
*/

@set_magic_quotes_runtime(FALSE); // intentionally @



/**
 * Nette\Callback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return Nette\Callback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof Nette\Callback) ? $callback : new Nette\Callback($callback, $m);
}



/**
 * Nette\Debug::dump shortcut.
 */
if (!function_exists('dump')) {
	function dump($var)
	{
		foreach (func_get_args() as $arg) Nette\Debug::dump($arg);
		return $var;
	}
}



require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/Framework.php';
require_once __DIR__ . '/Object.php';
require_once __DIR__ . '/ObjectMixin.php';
require_once __DIR__ . '/Callback.php';
require_once __DIR__ . '/Loaders/LimitedScope.php';
require_once __DIR__ . '/Loaders/AutoLoader.php';
require_once __DIR__ . '/Loaders/NetteLoader.php';


Nette\Loaders\NetteLoader::getInstance()->base = __DIR__;
Nette\Loaders\NetteLoader::getInstance()->register();
