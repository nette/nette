<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Loaders;

use Nette;


/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 */
class NetteLoader
{
	/** @var NetteLoader */
	private static $instance;

	/** @var array */
	public $renamed = array(
		'Nette\Config\Configurator' => 'Nette\Configurator',
		'Nette\Config\CompilerExtension' => 'Nette\DI\CompilerExtension',
		'Nette\Http\User' => 'Nette\Security\User',
		'Nette\Templating\DefaultHelpers' => 'Nette\Templating\Helpers',
		'Nette\Latte\ParseException' => 'Latte\CompileException',
		'Nette\Latte\CompileException' => 'Latte\CompileException',
		'Nette\Templating\FilterException' => 'Latte\CompileException',
		'Nette\Utils\PhpGenerator\ClassType' => 'Nette\PhpGenerator\ClassType',
		'Nette\Utils\PhpGenerator\Helpers' => 'Nette\PhpGenerator\Helpers',
		'Nette\Utils\PhpGenerator\Method' => 'Nette\PhpGenerator\Method',
		'Nette\Utils\PhpGenerator\Parameter' => 'Nette\PhpGenerator\Parameter',
		'Nette\Utils\PhpGenerator\PhpLiteral' => 'Nette\PhpGenerator\PhpLiteral',
		'Nette\Utils\PhpGenerator\Property' => 'Nette\PhpGenerator\Property',
	);

	/** @var array */
	public $list = array(
		'Nette\ArrayHash' => 'deprecated/ArrayHash',
		'Nette\ArrayList' => 'deprecated/ArrayList',
		'Nette\Caching\Storages\PhpFileStorage' => 'deprecated/Caching/PhpFileStorage',
		'Nette\Callback' => 'deprecated/Callback',
		'Nette\DI\Extensions\NetteAccessor' => 'deprecated/DI/NetteAccessor',
		'Nette\DateTime' => 'deprecated/DateTime',
		'Nette\Diagnostics\Bar' => 'deprecated/Diagnostics/Bar',
		'Nette\Diagnostics\BlueScreen' => 'deprecated/Diagnostics/BlueScreen',
		'Nette\Diagnostics\Debugger' => 'deprecated/Diagnostics/Debugger',
		'Nette\Diagnostics\Dumper' => 'deprecated/Diagnostics/Dumper',
		'Nette\Diagnostics\FireLogger' => 'deprecated/Diagnostics/FireLogger',
		'Nette\Diagnostics\Helpers' => 'deprecated/Diagnostics/Helpers',
		'Nette\Diagnostics\IBarPanel' => 'deprecated/Diagnostics/IBarPanel',
		'Nette\Diagnostics\Logger' => 'deprecated/Diagnostics/Logger',
		'Nette\Diagnostics\OutputDebugger' => 'deprecated/Diagnostics/OutputDebugger',
		'Nette\Image' => 'deprecated/Image',
		'Nette\Latte\Compiler' => 'deprecated/Latte/Compiler',
		'Nette\Latte\Engine' => 'deprecated/Latte/Engine',
		'Nette\Latte\HtmlNode' => 'deprecated/Latte/HtmlNode',
		'Nette\Latte\IMacro' => 'deprecated/Latte/IMacro',
		'Nette\Latte\MacroNode' => 'deprecated/Latte/MacroNode',
		'Nette\Latte\Macros\CoreMacros' => 'deprecated/Latte/Macros/CoreMacros',
		'Nette\Latte\Macros\MacroSet' => 'deprecated/Latte/Macros/MacroSet',
		'Nette\Latte\Parser' => 'deprecated/Latte/Parser',
		'Nette\Latte\PhpWriter' => 'deprecated/Latte/PhpWriter',
		'Nette\Latte\Token' => 'deprecated/Latte/Token',
		'Nette\ObjectMixin' => 'deprecated/ObjectMixin',
		'Nette\Templating\FileTemplate' => 'deprecated/Templating/FileTemplate',
		'Nette\Templating\Helpers' => 'deprecated/Templating/Helpers',
		'Nette\Templating\IFileTemplate' => 'deprecated/Templating/IFileTemplate',
		'Nette\Templating\ITemplate' => 'deprecated/Templating/ITemplate',
		'Nette\Templating\Template' => 'deprecated/Templating/Template',
		'Nette\Utils\LimitedScope' => 'deprecated/Utils/LimitedScope',
		'Nette\Utils\MimeTypeDetector' => 'deprecated/Utils/MimeTypeDetector',
	);


	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new static;
		}
		return self::$instance;
	}


	/**
	 * Register autoloader.
	 * @param  bool  prepend autoloader?
	 * @return void
	 */
	public function register($prepend = FALSE)
	{
		spl_autoload_register(array($this, 'tryLoad'), TRUE, (bool) $prepend);
	}


	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim($type, '\\');
		if (isset($this->renamed[$type])) {
			class_alias($this->renamed[$type], $type);
			trigger_error("Class $type has been renamed to {$this->renamed[$type]}.", E_USER_WARNING);

		} elseif (isset($this->list[$type])) {
			require __DIR__ . '/../' . $this->list[$type] . '.php';

		} elseif (substr($type, 0, 6) === 'Nette\\' && is_file($file = __DIR__ . '/../' . strtr(substr($type, 5), '\\', '/') . '.php')) {
			require $file;
		}
	}

}
