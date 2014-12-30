<?php

/**
 * Nette Framework (version 2.2.7 released on 2015-01-06, http://nette.org)
 *
 * Copyright (c) 2004, 2014 David Grudl (http://davidgrudl.com)
 */


require_once __DIR__ . '/Loaders/NetteLoader.php';

Nette\Loaders\NetteLoader::getInstance()->register();

class_alias('Nette\Configurator', 'Nette\Config\Configurator');
class_alias('Nette\DI\CompilerExtension', 'Nette\Config\CompilerExtension');
class_alias('Tracy\Bar', 'Nette\Diagnostics\Bar');
class_alias('Tracy\BlueScreen', 'Nette\Diagnostics\BlueScreen');
class_alias('Tracy\Dumper', 'Nette\Diagnostics\Dumper');
class_alias('Latte\CompileException', 'Nette\Latte\CompileException');
class_alias('Latte\IMacro', 'Nette\Latte\IMacro');
class_alias('Latte\Macros\MacroSet', 'Nette\Latte\Macros\MacroSet');
class_alias('Nette\Utils\ArrayHash', 'Nette\ArrayHash');
class_alias('Nette\Utils\ArrayList', 'Nette\ArrayList');
class_alias('Nette\Utils\DateTime', 'Nette\DateTime');
class_alias('Nette\Utils\Image', 'Nette\Image');
class_alias('Nette\Utils\ObjectMixin', 'Nette\ObjectMixin');
class_alias('Nette\Neon\Exception', 'Nette\Utils\NeonException');
class_alias('Nette\Neon\Entity', 'Nette\Utils\NeonEntity');
class_alias('Nette\Neon\Neon', 'Nette\Utils\Neon');

require_once __DIR__ . '/shortcuts.php';
