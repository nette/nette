<?php

defined('NETTE_ALIASES') && empty(NETTE_ALIASES) || array_walk(new ArrayIterator(array(
	'Nette\Config\Configurator' => 'Nette\Configurator',
	'Nette\Config\CompilerExtension' => 'Nette\DI\CompilerExtension',
	'Nette\Diagnostics\Bar' => 'Tracy\Bar',
	'Nette\Diagnostics\BlueScreen' => 'Tracy\BlueScreen',
	'Nette\Diagnostics\Dumper' => 'Tracy\Dumper',
	'Nette\Diagnostics\FireLogger' => 'Tracy\FireLogger',
	'Nette\Diagnostics\Logger' => 'Tracy\Logger',
	'Nette\Latte\ParseException' => 'Latte\CompileException',
	'Nette\Latte\CompileException' => 'Latte\CompileException',
	'Nette\Latte\Compiler' => 'Latte\Compiler',
	'Nette\Latte\IMacro' => 'Latte\IMacro',
	'Nette\Latte\Parser' => 'Latte\Parser',
	'Nette\Latte\Macros\CoreMacros' => 'Latte\Macros\CoreMacros',
	'Nette\Latte\Macros\MacroSet' => 'Latte\Macros\MacroSet',
	'Nette\ArrayHash' => 'Nette\Utils\ArrayHash',
	'Nette\ArrayList' => 'Nette\Utils\ArrayList',
	'Nette\DateTime' => 'Nette\Utils\DateTime',
	'Nette\Image' => 'Nette\Utils\Image',
	'Nette\ObjectMixin' => 'Nette\Utils\ObjectMixin',
)), 'class_alias');
