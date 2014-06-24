<?php

/**
 * Nette Framework (version 2.2.2 released on 2014-06-26, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */


if (PHP_VERSION_ID < 50301) {
	throw new Exception('Nette Framework requires PHP 5.3.1 or newer.');
}


// Run NetteLoader
require_once __DIR__ . '/Loaders/NetteLoader.php';

Nette\Loaders\NetteLoader::getInstance()->register();

array_walk(new ArrayIterator(array(
	'Nette\Config\Configurator' => 'Nette\Configurator',
	'Nette\Config\CompilerExtension' => 'Nette\DI\CompilerExtension',
	'Nette\Diagnostics\Bar' => 'Tracy\Bar',
	'Nette\Diagnostics\BlueScreen' => 'Tracy\BlueScreen',
	'Nette\Diagnostics\Dumper' => 'Tracy\Dumper',
	'Nette\Latte\CompileException' => 'Latte\CompileException',
	'Nette\Latte\IMacro' => 'Latte\IMacro',
	'Nette\Latte\Macros\MacroSet' => 'Latte\Macros\MacroSet',
	'Nette\ArrayHash' => 'Nette\Utils\ArrayHash',
	'Nette\ArrayList' => 'Nette\Utils\ArrayList',
	'Nette\DateTime' => 'Nette\Utils\DateTime',
	'Nette\Image' => 'Nette\Utils\Image',
	'Nette\ObjectMixin' => 'Nette\Utils\ObjectMixin',
	'Nette\Utils\NeonException' => 'Nette\Neon\Exception',
	'Nette\Utils\NeonEntity' => 'Nette\Neon\Entity',
	'Nette\Utils\Neon' => 'Nette\Neon\Neon',
)), 'class_alias');

require_once __DIR__ . '/shortcuts.php';
