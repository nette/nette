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
			if (is_string($definition)) {
				$definition = array('class' => $definition);
			}
			array_walk_recursive($definition, function(&$val) use ($container) {
				$val = $container->expand($val);
			});
			list($factory, $tags) = $this->parseDefinition($definition);
			$container->addService($name, $factory, $tags);
		}
	}



	private function parseDefinition(array $definition)
	{
		$arguments = isset($definition['arguments']) ? $definition['arguments'] : array();

		if (isset($definition['class'])) {
			$factory = $definition['class'];
			$methods = isset($definition['methods']) ? $definition['methods'] : array();

			if ($methods || $arguments) {
				$factory = function($container) use ($factory, $arguments, $methods) {
					$expander = function(&$val) use ($container) {
						$val = $val[0] === '@' ? $container->getService(substr($val, 1)) : $val;
					};

					array_walk_recursive($arguments, $expander);
					$service = $arguments
						? Nette\Reflection\ClassType::from($factory)->newInstanceArgs($arguments)
						: new $factory;

					array_walk_recursive($methods, $expander);
					foreach ($methods as $method) {
						call_user_func_array(array($service, $method[0]), isset($method[1]) ? $method[1] : array());
					}

					return $service;
				};
			}

		} elseif (isset($definition['factory'])) {
			$factory = $definition['factory'];
			if ($arguments) {
				$factory = function($container) use ($factory, $arguments) {
					array_walk_recursive($arguments, function(&$val) use ($container) {
						$val = $val[0] === '@' ? $container->getService(substr($val, 1)) : $val;
					});
					array_unshift($arguments, $container);
					return call_user_func_array($factory, $arguments);
				};
			}
		} else {
			throw new Nette\InvalidStateException("Factory method is not specified.");
		}

		return array($factory, isset($definition['tags']) ? (array) $definition['tags'] : NULL);
	}

}
