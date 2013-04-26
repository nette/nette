<?php

/**
 * Nette Framework (version 2.1-dev released on $WCDATE$, http://nette.org)
 *
 * Copyright (c) 2004, 2013 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */



/**
 * Check and reset PHP configuration.
 */
/*5.2*
if (!defined('PHP_VERSION_ID')) {
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

if (PHP_VERSION_ID < 50200) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}
*/
@header('Content-Type: text/html; charset=utf-8'); // @ - headers may be sent



/**
 * Load and configure Nette Framework.
 */
define('NETTE', TRUE);
define('NETTE_DIR', __DIR__);
define('NETTE_VERSION_ID', 20100); // v2.1.0
define('NETTE_PACKAGE', '5.3');



require_once __DIR__ . '/common/exceptions.php';
require_once __DIR__ . '/common/Object.php';
require_once __DIR__ . '/Utils/LimitedScope.php';
require_once __DIR__ . '/Loaders/AutoLoader.php';
require_once __DIR__ . '/Loaders/NetteLoader.php';


Nette\Loaders\NetteLoader::getInstance()->register();
class_alias('Nette\Configurator', 'Nette\Config\Configurator');

require_once __DIR__ . '/Diagnostics/Helpers.php';
require_once __DIR__ . '/Diagnostics/shortcuts.php';
require_once __DIR__ . '/Utils/Html.php';
Nette\Diagnostics\Debugger::_init();

Nette\Utils\SafeStream::register();



/**
 * Nette\Callback factory.
 * @param  mixed   class, object, callable
 * @param  string  method
 * @return Nette\Callback
 */
function callback($callback, $m = NULL)
{
	return new Nette\Callback($callback, $m);
}
