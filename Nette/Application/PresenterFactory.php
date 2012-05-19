<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Application;

use Nette;



/**
 * Default presenter loader.
 *
 * @author     David Grudl
 */
class PresenterFactory implements IPresenterFactory
{
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var string[] of module => mask */
	public $mapping = array(
		'*' => '\*Module\*Presenter',
		'Nette' => 'NetteModule\*\*Presenter',
	);

	/** @var string */
	private $baseDir;

	/** @var array */
	private $cache = array();

	/** @var Nette\DI\Container */
	private $container;



	/**
	 * @param  string
	 */
	public function __construct($baseDir, Nette\DI\Container $container)
	{
		$this->baseDir = $baseDir;
		$this->container = $container;
	}



	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$presenter = $this->container->createInstance($this->getPresenterClass($name));
		foreach (array_reverse(get_class_methods($presenter)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->container->callMethod(array($presenter, $method));
			}
		}

		if ($presenter instanceof UI\Presenter && $presenter->invalidLinkMode === NULL) {
			$presenter->invalidLinkMode = $this->container->parameters['debugMode'] ? UI\Presenter::INVALID_LINK_WARNING : UI\Presenter::INVALID_LINK_SILENT;
		}
		return $presenter;
	}



	/**
	 * @param  string  presenter name
	 * @return string  class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name) = $this->cache[$name];
			return $class;
		}

		if (!is_string($name) || !Nette\Utils\Strings::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				Nette\Utils\LimitedScope::load($file, TRUE);
			}

			if (!class_exists($class)) {
				throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found in '$file'.");
			}
		}

		$reflection = new Nette\Reflection\ClassType($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}

		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}



	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		/*5.2*return strtr($presenter, ':', '_') . 'Presenter';*/
		$parts = explode(':', $presenter);
		$mapping = explode('\\*', isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*']);
		$class = $mapping[0];
		while ($part = array_shift($parts)) {
			$class .= ($class ? '\\' : '') . $part . $mapping[$parts ? 1 : 2];
		}
		return $class;
	}



	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		/*5.2*return strtr(substr($class, 0, -9), '_', ':');*/
		foreach ($this->mapping as $module => $mapping) {
			$mapping = explode('\\\\\*', preg_quote($mapping, '#'));
			$mapping[0] .= $mapping[0] ? '\\\\' : '';
			if (preg_match("#^\\\\?$mapping[0]((?:\w+$mapping[1]\\\\)*)(\w+)$mapping[2]$#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
					. str_replace($mapping[1] . '\\', ':', $matches[1]) . $matches[2];
			}
		}
	}



	/**
	 * Formats presenter class file name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterFile($presenter)
	{
		$path = '/' . str_replace(':', 'Module/', $presenter);
		return $this->baseDir . substr_replace($path, '/presenters', strrpos($path, '/'), 0) . 'Presenter.php';
	}

}
