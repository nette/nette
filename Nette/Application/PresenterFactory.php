<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Application;

use Nette;


/**
 * Default presenter loader.
 *
 * @author     David Grudl
 */
class PresenterFactory extends Nette\Object implements IPresenterFactory
{
	/** @var bool */
	public $caseSensitive = FALSE;

	/** @var array[] of module => splited mask */
	private $mapping = array(
		'*' => array('', '*Module\\', '*Presenter'),
		'Nette' => array('NetteModule\\', '*\\', '*Presenter'),
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
	 * Creates new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		if (count($services = $this->container->findByType($class)) === 1) {
			$presenter = $this->container->createService($services[0]);
		} else {
			$presenter = $this->container->createInstance($class);
		}
		$this->container->callInjects($presenter);

		if ($presenter instanceof UI\Presenter && $presenter->invalidLinkMode === NULL) {
			$presenter->invalidLinkMode = $this->container->parameters['debugMode'] ? UI\Presenter::INVALID_LINK_WARNING : UI\Presenter::INVALID_LINK_SILENT;
		}
		return $presenter;
	}


	/**
	 * Generates and checks presenter class name.
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

		if (!is_string($name) || !Nette\Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClass($name);

		if (!class_exists($class)) {
			// internal autoloading
			$file = $this->formatPresenterFile($name);
			if (is_file($file) && is_readable($file)) {
				call_user_func(function() use ($file) { require $file; });
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
	 * Sets mapping as pairs [module => mask]
	 * @return self
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
				throw new Nette\InvalidStateException("Invalid mapping mask '$mask'.");
			}
			$this->mapping[$module] = array($m[1], $m[2] ?: '*Module\\', $m[3]);
		}
		return $this;
	}


	/**
	 * Formats presenter class name from its name.
	 * @param  string
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		$parts = explode(':', $presenter);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}
		return $mapping[0];
	}


	/**
	 * Formats presenter name from class name.
	 * @param  string
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(array('\\', '*'), array('\\\\', '(\w+)'), $mapping);
			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
					. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
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
