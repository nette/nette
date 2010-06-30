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

@set_magic_quotes_runtime(FALSE); // intentionally @



/**
 * Load and configure Nette Framework
 */
define('NETTE', TRUE);
define('NETTE_VERSION_ID', 906); // v0.9.6
define('NETTE_PACKAGE', '5.3');



require_once __DIR__ . '/Utils/shortcuts.php';
require_once __DIR__ . '/Utils/exceptions.php';
require_once __DIR__ . '/Utils/Framework.php';
require_once __DIR__ . '/Utils/Object.php';
require_once __DIR__ . '/Utils/ObjectMixin.php';
require_once __DIR__ . '/Utils/Callback.php';
require_once __DIR__ . '/Loaders/LimitedScope.php';
require_once __DIR__ . '/Loaders/AutoLoader.php';
require_once __DIR__ . '/Loaders/NetteLoader.php';


Nette\Loaders\NetteLoader::getInstance()->base = __DIR__;
Nette\Loaders\NetteLoader::getInstance()->register();
