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

	/**
	 * Adds new services from list of definitions. Expands %param% and @service values.
	 * Format:
	 *   serviceName => array(
	 *      class => 'ClassName' or factory => 'Factory::create'
	 *      arguments => array(...)
	 *      methods => array(
	 *         array(methodName, array(...))
	 *         ...
	 *      )
	 *      tags => array(...)
	 *   )
	 */
	public function addDefinitions(IContainer $container, array $definitions)
	{
		foreach ($definitions as $name => $definition) {
			if (is_scalar($definition)) {
				if (substr($definition, 0, 1) === '@') {
					$definition = array('alias' => substr($definition, 1));
				} else {
					$definition = array('class' => $definition);
				}
			}

			$arguments = isset($definition['arguments']) ? $definition['arguments'] : array();
			$expander = function(&$val) use ($container) {
				$val = substr($val, 0, 1) === '@' ? $container->getService(substr($val, 1)) : $container->expand($val);
			};

			if (isset($definition['class']) || isset($definition['factory'])) {
				if (isset($definition['class'])) {
					$class = $definition['class'];
				} else {
					$class = NULL;
					array_unshift($arguments, $definition['factory']);
				}
				$methods = isset($definition['methods']) ? $definition['methods'] : array();
				$factory = function($container) use ($class, $arguments, $methods, $expander) {
					array_walk_recursive($arguments, $expander);
					if ($class) {
						$class = $container->expand($class);
						if ($arguments) {
							$service = Nette\Reflection\ClassType::from($class)->newInstanceArgs($arguments);
						} else {
							$service = new $class;
						}
					} else {
						$factory = $arguments[0]; $arguments[0] = $container;
						$service = call_user_func_array($factory, $arguments);
					}

					array_walk_recursive($methods, $expander);
					foreach ($methods as $method) {
						call_user_func_array(array($service, $method[0]), isset($method[1]) ? $method[1] : array());
					}

					return $service;
				};

			} elseif (isset($definition['alias'])) {
				$factory = function($container) use ($definition) {
					return $container->getService($definition['alias']);
				};
			} else {
				throw new Nette\InvalidStateException("The definition of service '$name' is missing factory method.");
			}

			if (isset($definition['tags'])) {
				$tags = (array) $definition['tags'];
				array_walk_recursive($tags, $expander);
			} else {
				$tags = NULL;
			}
			$container->addService($name, $factory, $tags);
		}
	}



	public function generateCode(array $definitions)
	{
		$code = '';
		foreach ($definitions as $name => $definition) {
			$name = $this->varExport($name);
			if (is_scalar($definition)) {
				if (substr($definition, 0, 1) === '@') {
					$definition = array('alias' => substr($definition, 1));
				} else {
					$factory = $this->varExport($definition);
					$code .= "\$container->addService($name, $factory);\n\n";
					continue;
				}
			}

			if (isset($definition['class']) || isset($definition['factory'])) {
				$arguments = $this->argsExport(isset($definition['arguments']) ? $definition['arguments'] : array());
				$factory = "function(\$container) {\n\t";
				$factory .= isset($definition['class'])
					? '$class = ' . $this->argsExport(array($definition['class'])) . '; $service = new $class(' . $arguments . ");\n"
					: "\$service = call_user_func(\n\t\t" . $this->argsExport(array($definition['factory']))
						. ",\n\t\t\$container" . ($arguments ? ",\n\t\t$arguments" : '') . "\n\t);\n";

				if (isset($definition['methods'])) {
					foreach ($definition['methods'] as $method) {
						$args = isset($method[1]) ? $this->argsExport($method[1]) : '';
						$factory .= "\t\$service->$method[0]($args);\n";
					}
				}
				$factory .= "\treturn \$service;\n}";

			} elseif (isset($definition['alias'])) {
				$factory = $this->varExport($definition['alias']);
				$factory = "function(\$container) {\n\treturn \$container->getService($factory);\n}";
			} else {
				throw new Nette\InvalidStateException("The definition of service '$name' is missing factory method.");
			}

			$tags = isset($definition['tags']) ? $this->argsExport(array($definition['tags'])) : 'NULL';
			$code .= "\$container->addService($name, $factory, $tags);\n\n";
		}
		return $code;
	}



	private function argsExport($args)
	{
		$args = implode(', ', array_map(array($this, 'varExport'), $args));
		$args = preg_replace("#(?<!\\\)'@(\w+)'#", '\$container->getService(\'$1\')', $args);
		$args = preg_replace("#(?<!\\\)'(?:[^'\\\]|\\\.)*%(?:[^'\\\]|\\\.)*'#", '\$container->expand($0)', $args);
		return $args;
	}



	private function varExport($arg)
	{
		return preg_replace('#\n *#', ' ', var_export($arg, TRUE));
	}

}
