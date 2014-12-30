<?php

/**
 * Nette Framework (version 2.1.9 released on 2015-01-06, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */


// deprecated
define('NETTE', TRUE);
define('NETTE_DIR', __DIR__);
define('NETTE_VERSION_ID', 20109);


// Run NetteLoader
require_once __DIR__ . '/common/exceptions.php';
require_once __DIR__ . '/common/Object.php';
require_once __DIR__ . '/Loaders/NetteLoader.php';

Nette\Loaders\NetteLoader::getInstance()->register();


require_once __DIR__ . '/common/shortcuts.php';
