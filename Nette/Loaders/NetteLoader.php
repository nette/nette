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
class NetteLoader extends Nette\Object
{
	/** @var NetteLoader */
	private static $instance;

	/** @var array */
	public $renamed = array(
		'Nette\Config\Configurator' => 'Nette\Configurator',
		'Nette\Config\CompilerExtension' => 'Nette\DI\CompilerExtension',
		'Nette\Http\User' => 'Nette\Security\User',
		'Nette\Templating\DefaultHelpers' => 'Nette\Templating\Helpers',
		'Nette\Latte\ParseException' => 'Nette\Latte\CompileException',
		'Nette\Utils\PhpGenerator\ClassType' => 'Nette\PhpGenerator\ClassType',
		'Nette\Utils\PhpGenerator\Helpers' => 'Nette\PhpGenerator\Helpers',
		'Nette\Utils\PhpGenerator\Method' => 'Nette\PhpGenerator\Method',
		'Nette\Utils\PhpGenerator\Parameter' => 'Nette\PhpGenerator\Parameter',
		'Nette\Utils\PhpGenerator\PhpLiteral' => 'Nette\PhpGenerator\PhpLiteral',
		'Nette\Utils\PhpGenerator\Property' => 'Nette\PhpGenerator\Property',
	);

	/** @var array */
	public $list = array(
		'NetteModule\ErrorPresenter' => '/Application/ErrorPresenter',
		'NetteModule\MicroPresenter' => '/Application/MicroPresenter',
		'Nette\Application\AbortException' => '/Application/exceptions',
		'Nette\Application\ApplicationException' => '/Application/exceptions',
		'Nette\Application\BadRequestException' => '/Application/exceptions',
		'Nette\Application\ForbiddenRequestException' => '/Application/exceptions',
		'Nette\Application\InvalidPresenterException' => '/Application/exceptions',
		'Nette\ArgumentOutOfRangeException' => '/common/exceptions',
		'Nette\ArrayHash' => '/common/ArrayHash',
		'Nette\ArrayList' => '/common/ArrayList',
		'Nette\Callback' => '/common/Callback',
		'Nette\Configurator' => '/common/Configurator',
		'Nette\Database\Reflection\AmbiguousReferenceKeyException' => '/Database/Reflection/exceptions',
		'Nette\Database\Reflection\MissingReferenceException' => '/Database/Reflection/exceptions',
		'Nette\DI\MissingServiceException' => '/DI/exceptions',
		'Nette\DI\ServiceCreationException' => '/DI/exceptions',
		'Nette\DateTime' => '/common/DateTime',
		'Nette\DeprecatedException' => '/common/exceptions',
		'Nette\DirectoryNotFoundException' => '/common/exceptions',
		'Nette\Environment' => '/common/Environment',
		'Nette\FatalErrorException' => '/common/exceptions',
		'Nette\FileNotFoundException' => '/common/exceptions',
		'Nette\Framework' => '/common/Framework',
		'Nette\FreezableObject' => '/common/FreezableObject',
		'Nette\IFreezable' => '/common/IFreezable',
		'Nette\IOException' => '/common/exceptions',
		'Nette\Image' => '/common/Image',
		'Nette\InvalidArgumentException' => '/common/exceptions',
		'Nette\InvalidStateException' => '/common/exceptions',
		'Nette\Latte\CompileException' => '/Latte/exceptions',
		'Nette\Mail\SmtpException' => '/Mail/SmtpMailer',
		'Nette\MemberAccessException' => '/common/exceptions',
		'Nette\NotImplementedException' => '/common/exceptions',
		'Nette\NotSupportedException' => '/common/exceptions',
		'Nette\Object' => '/common/Object',
		'Nette\ObjectMixin' => '/common/ObjectMixin',
		'Nette\OutOfRangeException' => '/common/exceptions',
		'Nette\StaticClassException' => '/common/exceptions',
		'Nette\UnexpectedValueException' => '/common/exceptions',
		'Nette\UnknownImageFileException' => '/common/Image',
		'Nette\Utils\AssertionException' => '/Utils/Validators',
		'Nette\Utils\JsonException' => '/Utils/Json',
		'Nette\Utils\NeonEntity' => '/Utils/Neon',
		'Nette\Utils\NeonException' => '/Utils/Neon',
		'Nette\Utils\RegexpException' => '/Utils/Strings',
		'Nette\Utils\TokenizerException' => '/Utils/Tokenizer',
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
