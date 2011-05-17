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
			if (!is_array($definition)) {
				$definition = array('class' => $definition);
			}

			$arguments = isset($definition['arguments']) ? $definition['arguments'] : array();
			$expander = function(&$val) use ($container) {
				$val = $val[0] === '@' ? $container->getService(substr($val, 1)) : $container->expand($val);
			};

			if (isset($definition['class'])) {
				$class = $definition['class'];
				$methods = isset($definition['methods']) ? $definition['methods'] : array();
				$factory = function($container) use ($class, $arguments, $methods, $expander) {
					$class = $container->expand($class);
					if ($arguments) {
						array_walk_recursive($arguments, $expander);
						$service = Nette\Reflection\ClassType::from($class)->newInstanceArgs($arguments);
					} else {
						$service = new $class;
					}

					array_walk_recursive($methods, $expander);
					foreach ($methods as $method) {
						call_user_func_array(array($service, $method[0]), isset($method[1]) ? $method[1] : array());
					}

					return $service;
				};

			} elseif (isset($definition['factory'])) {
				array_unshift($arguments, $definition['factory']);
				$factory = function($container) use ($arguments, $expander) {
					array_walk_recursive($arguments, $expander);
					$factory = $arguments[0]; $arguments[0] = $container;
					return call_user_func_array($factory, $arguments);
				};
			} else {
				throw new Nette\InvalidStateException("Factory method is not specified.");
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

}
