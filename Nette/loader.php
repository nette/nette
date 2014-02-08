<?php

/**
 * Nette Framework (version 2.1.1 released on 2013-12-31, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */


if (PHP_VERSION_ID < 50301) {
	throw new Exception('Nette Framework requires PHP 5.3.1 or newer.');
}


// deprecated
define('NETTE', TRUE);
define('NETTE_DIR', __DIR__);
define('NETTE_VERSION_ID', 20101);


// Run NetteLoader
require_once __DIR__ . '/common/exceptions.php';
require_once __DIR__ . '/common/Object.php';
require_once __DIR__ . '/Loaders/NetteLoader.php';

Nette\Loaders\NetteLoader::getInstance()->register();


require_once __DIR__ . '/common/shortcuts.php';
