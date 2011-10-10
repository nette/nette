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

	/** @var array */
	private $classes = array(
		'nette\di\container' => array(TRUE => array('self')),
		'nette\di\icontainer' => array(TRUE => array('self')),
	);



	/**
	 * Adds new services from list of definitions. Expands %param% and @service values.
	 * @param  string  class or interface
	 * @param  string
	 * @param  bool
	 * @return ServiceDefinition
	 */
	public function addDefinition($name, $class, $prefer = FALSE)
	{
		if (isset($this->definitions[$name])) {
			throw new Nette\InvalidStateException("Service '$name' has already been added.");
		}

		if ($class && self::isExpanded($class)) {
			if (!class_exists($class) && !interface_exists($class)) {
				throw new Nette\InvalidStateException("Class '$class' has not been found.");
			}
			foreach (class_parents($class) + class_implements($class) + array($class) as $parent) {
				$this->classes[strtolower($parent)][(bool) $prefer][] = $name;
			}
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
			$code .= '$service = ';

			if (preg_match('#^@\w+$#', $factory[0]) && self::isExpanded($factory[1])) {
				if (isset($this->definitions[substr($factory[0], 1)]->class)) {
					$arguments = $this->autowireArguments($this->definitions[substr($factory[0], 1)]->class, $factory[1], $arguments);
				}
				$code .= $this->argsExport(array($factory[0])) . "->$factory[1](";

			} elseif (self::isExpanded($factory[0]) && self::isExpanded($factory[1])) {
				$arguments = $this->autowireArguments($factory[0], $factory[1], $arguments);
				$code .= implode('::', $factory) . '(';

			} else {
				$code .= 'call_user_func(' . $this->argsExport(array($factory)) . ', ';
			}
			$code .= $this->argsExport($arguments) . ");\n";

		} else { // class
			if (self::isExpanded($definition->class)) {
				$arguments = $this->autowireArguments($definition->class, '__construct', $arguments);
				$code .= "\$service = new $definition->class";
			} else {
				$code .= '$class = ' . $this->argsExport(array($definition->class)) . ";\n" . '$service = new $class';
			}
			$code .= $arguments ? "({$this->argsExport($arguments)});\n" : ";\n";
		}

		foreach ((array) $definition->methods as $method) {
			$arguments = is_array($method[1]) ? $method[1] : array();
			if (self::isExpanded($method[0])) {
				if ($definition->class && self::isExpanded($definition->class)) {
					$arguments = $this->autowireArguments($definition->class, $method[0], $arguments);
				}
				$code .= "\$service->$method[0]";
			} else {
				$code .= '$method = ' . $this->argsExport(array($method[0])) . '; $service->$method';
			}
			$code .= "({$this->argsExport($arguments)});\n";
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



	/**
	 * Resolves service name.
	 * @param  string  class or interface
	 * @return string  service name or NULL
	 * @throws ServiceCreationException
	 */
	public function resolve($class)
	{
		$classes = & $this->classes[ltrim(strtolower($class), '\\')];
		if (isset($classes[TRUE]) && count($classes[TRUE]) === 1) {
			return $classes[TRUE][0];

		} elseif (!isset($classes[TRUE]) && isset($classes[FALSE]) && count($classes[FALSE]) === 1) {
			return $classes[FALSE][0];

		} elseif (isset($classes[TRUE])) {
			throw new ServiceCreationException("Matched multiple preferred services of type '$class' found: " . implode(', ', $classes[TRUE]));

		} elseif (isset($classes[FALSE])) {
			throw new ServiceCreationException("Matched multiple services of type '$class' found: " . implode(', ', $classes[FALSE]));
		}
	}



	/**
	 * Process autowiring on arguments.
	 * @return array
	 */
	public function autowireArguments($class, $method, array $arguments)
	{
		$optCount = 0;
		$num = -1;
		$res = array();
		try {
			$rm = Nette\Reflection\Method::from($class, $method);
			if ($rm->isAbstract() || !$rm->isPublic()) {
				throw new ServiceCreationException("$rm is not callable.");
			}

			foreach ($rm->getParameters() as $num => $parameter) {
				if (array_key_exists($num, $arguments)) {
					$res[$num] = $arguments[$num];
					unset($arguments[$num]);
					$optCount = 0;

				} elseif (array_key_exists($parameter->getName(), $arguments)) {
					$res[$num] = $arguments[$parameter->getName()];
					unset($arguments[$parameter->getName()]);
					$optCount = 0;

				} elseif ($parameter->getClass()) {
					$service = $this->resolve($parameter->getClass()->getName());
					if ($service === NULL) {
						if ($parameter->allowsNull()) {
							$res[$num] = NULL;
							$optCount++;
						} else {
							throw new ServiceCreationException("Matched no service of type {$parameter->getClass()->getName()} found");
						}
					} else {
						$res[$num] = '@' . $service;
						$optCount = 0;
					}

				} elseif ($parameter->isOptional()) {
					$res[$num] = $parameter->getDefaultValue();
					$optCount++;

				} elseif ($parameter->allowsNull()) {
					$res[$num] = NULL;
					$optCount = 0;

				} else {
					throw new ServiceCreationException("$parameter is missing.");
				}
			}

		} catch (\ReflectionException $e) {
			if ($arguments && $method === '__construct') {
				throw new ServiceCreationException("Unable to pass arguments, class $class has not constructor.");
			}
		}

		// extra parameters
		while (array_key_exists(++$num, $arguments)) {
			$res[$num] = $arguments[$num];
			unset($arguments[$num]);
			$optCount = 0;
		}
		if ($arguments) {
			throw new ServiceCreationException("Unexcepted parameters: " . implode(', ', array_keys($arguments)));
		}

		return $optCount ? array_slice($res, 0, -$optCount) : $res;
	}



	private static function argsExport($args)
	{
		$args = implode(', ', array_map(array(__CLASS__, 'varExport'), $args));
		$args = preg_replace("#(?<!\\\)'@self'#", '\$container', $args);
		$args = preg_replace("#(?<!\\\)'@([a-zA-Z_]\w*)'#", '\$container->$1', $args);
		$args = preg_replace("#(?<!\\\)'@(\w+)'#", '\$container->{\'$1\'}', $args);
		$args = preg_replace("#(?<!\\\)'%([\w-]+)%'#", '\$container->params[\'$1\']', $args);
		$args = preg_replace("#(?<!\\\)'(?:[^'\\\]|\\\.)*%(?:[^'\\\]|\\\.)*'#", 'Nette\Utils\Strings::expand($0, \$container->params)', $args);
		return $args;
	}



	private static function varExport($arg)
	{
		return preg_replace('#\n *#', ' ', var_export($arg, TRUE));
	}



	private static function isExpanded($arg)
	{
		return strpos($arg, '%') === FALSE;
	}

}
