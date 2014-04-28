<?php

/**
 * Nette Framework (version 2.2-rc2 released on 2014-04-28, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */


if (PHP_VERSION_ID < 50301) {
	throw new Exception('Nette Framework requires PHP 5.3.1 or newer.');
}


// Run NetteLoader
require_once __DIR__ . '/Loaders/NetteLoader.php';

Nette\Loaders\NetteLoader::getInstance()->register();


require_once __DIR__ . '/aliases.php';
require_once __DIR__ . '/shortcuts.php';
