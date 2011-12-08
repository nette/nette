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
 * The DI helpers.
 *
 * @author     David Grudl
 */
final class Helpers
{

	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @param  array
	 * @param  bool
	 * @return mixed
	 * @throws Nette\InvalidArgumentException
	 */
	public static function expand($var, array $params, $recursive = FALSE)
	{
		if (is_array($var)) {
			$res = array();
			foreach ($var as $key => $val) {
				$res[$key] = self::expand($val, $params, $recursive);
			}
			return $res;

		} elseif ($var instanceof Statement) {
			return new Statement(self::expand($var->entity, $params, $recursive), self::expand($var->arguments, $params, $recursive));

		} elseif (!is_string($var)) {
			return $var;
		}

		$parts = preg_split('#%([\w.-]*)%#i', $var, -1, PREG_SPLIT_DELIM_CAPTURE);
		$res = '';
		foreach ($parts as $n => $part) {
			if ($n % 2 === 0) {
				$res .= $part;

			} elseif ($part === '') {
				$res .= '%';

			} elseif (isset($recursive[$part])) {
				throw new Nette\InvalidArgumentException('Circular reference detected for variables: ' . implode(', ', array_keys($recursive)) . '.');

			} else {
				$val = Nette\Utils\Arrays::get($params, explode('.', $part));
				if ($recursive && is_string($val)) {
					$val = self::expand($val, $params, (is_array($recursive) ? $recursive : array()) + array($part => 1));
				}
				if (strlen($part) + 2 === strlen($var)) {
					return $val;
				}
				if (!is_scalar($val)) {
					throw new Nette\InvalidArgumentException("Unable to concatenate non-scalar parameter '$part' into '$var'.");
				}
				$res .= $val;
			}
		}
		return $res;
	}



	/**
	 * Generates list of arguments using autowiring.
	 * @return array
	 */
	public static function autowireArguments(\ReflectionFunctionAbstract $method, array $arguments, $container)
	{
		$optCount = 0;
		$num = -1;
		$res = array();

		foreach ($method->getParameters() as $num => $parameter) {
			if (array_key_exists($num, $arguments)) {
				$res[$num] = $arguments[$num];
				unset($arguments[$num]);
				$optCount = 0;

			} elseif (array_key_exists($parameter->getName(), $arguments)) {
				$res[$num] = $arguments[$parameter->getName()];
				unset($arguments[$parameter->getName()]);
				$optCount = 0;

			} elseif ($parameter->getClass()) {
				$res[$num] = $container->findByClass($parameter->getClass()->getName());
				if ($res[$num] === NULL) {
					if ($parameter->allowsNull()) {
						$optCount++;
					} else {
						throw new Nette\InvalidArgumentException("No service of type {$parameter->getClass()->getName()} found");
					}
				} else {
					if ($container instanceof ContainerBuilder) {
						$res[$num] = '@' . $res[$num];
					}
					$optCount = 0;
				}

			} elseif ($parameter->isOptional()) {
				$res[$num] = $parameter->getDefaultValue();
				$optCount++;

			} else {
				throw new Nette\InvalidArgumentException("$parameter is missing.");
			}
		}

		// extra parameters
		while (array_key_exists(++$num, $arguments)) {
			$res[$num] = $arguments[$num];
			unset($arguments[$num]);
			$optCount = 0;
		}
		if ($arguments) {
			throw new Nette\InvalidArgumentException("Unable to pass specified arguments to $method.");
		}

		return $optCount ? array_slice($res, 0, -$optCount) : $res;
	}

}
