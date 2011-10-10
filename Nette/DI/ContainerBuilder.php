<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette;



/**
 * Basic container builder.
 *
 * @author     David Grudl
 */
class ContainerBuilder extends Nette\Object
{
	/** @var array */
	private $definitions = array();


	/**
	 * Adds new services from list of definitions. Expands %param% and @service values.
	 * @param  string  class or interface
	 * @param  string
	 * @return ServiceDefinition
	 */
	public function addDefinition($name, $class)
	{
		if (isset($this->definitions[$name])) {
			throw new Nette\InvalidStateException("Service '$name' has already been added.");
		}

		return $this->definitions[$name] = new ServiceDefinition($class);
	}



	/**
	 * Generates PHP code.
	 * @return string
	 */
	public function generateCode()
	{
		$code = '';
		foreach ($this->definitions as $name => $foo) {
			try {
				$code .= '$container->addService(' . $this->varExport($name) . ", function(\$container) {\n"
					. Nette\Utils\Strings::indent($this->generateFactory($name), 1)
					. "\n});\n\n";
			} catch (\Exception $e) {
				throw new ServiceCreationException("Error creating service '$name': {$e->getMessage()}", 0, $e);
			}
		}
		return $code;
	}



	private function generateFactory($name)
	{
		$definition = $this->definitions[$name];
		if (!$definition->class && !$definition->factory) {
			throw new ServiceCreationException("Class and factory method is missing.");
		}

		$arguments = (array) $definition->arguments;
		$code = '';

		if ($definition->factory) {
			$factory = is_array($definition->factory) ? $definition->factory : explode('::', $definition->factory);
			array_unshift($arguments, '@self');
			$code .= '$service = call_user_func(' . $this->argsExport(array($factory)) . ', ' . $this->argsExport($arguments) . ");\n";

		} else {
			$code .= '$class = ' . $this->argsExport(array($definition->class)) . ";\n"
				. '$service = new $class' . ($arguments ? "({$this->argsExport($arguments)});\n" : ";\n");
		}

		foreach ((array) $definition->methods as $method) {
			$arguments = is_array($method[1]) ? $method[1] : array();
			$code .= "\$service->$method[0]({$this->argsExport($arguments)});\n";
		}

		return $code .= 'return $service;';
	}



	/**
	 * Gets the service objects of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		$found = array();
		foreach ($this->definitions as $name => $definition) {
			if (isset($definition->tags[$tag])) {
				$found[$name] = $definition->tags[$tag];
			}
		}
		return $found;
	}



	private static function argsExport($args)
	{
		$args = implode(', ', array_map(array(__CLASS__, 'varExport'), $args));
		$args = preg_replace("#(?<!\\\)'@self'#", '\$container', $args);
		$args = preg_replace("#(?<!\\\)'@(\w+)'#", '\$container->getService(\'$1\')', $args);
		$args = preg_replace("#(?<!\\\)'%([\w-]+)%'#", '\$container->params[\'$1\']', $args);
		$args = preg_replace("#(?<!\\\)'(?:[^'\\\]|\\\.)*%(?:[^'\\\]|\\\.)*'#", 'Nette\Utils\Strings::expand($0, \$container->params)', $args);
		return $args;
	}



	private static function varExport($arg)
	{
		return preg_replace('#\n *#', ' ', var_export($arg, TRUE));
	}

}
