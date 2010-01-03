<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 */



@set_magic_quotes_runtime(FALSE); // intentionally @



/**/
/**
 * Fix for class::method callback in PHP < 5.2.2
 */
if (version_compare(PHP_VERSION , '5.2.2', '<')) {
	function fixCallback(& $callback)
	{
		// __invoke support
		if (is_object($callback)) {
			$callback = array($callback, '__invoke');
			return;
		}

		// explode 'Class::method' into array
		if (is_string($callback) && strpos($callback, ':')) {
			$callback = explode('::', $callback);
		}

		// remove class namespace
		if (is_array($callback) && is_string($callback[0]) && $a = strrpos($callback[0], '\\')) {
			$callback[0] = substr($callback[0], $a + 1);
		}
	}

} else {
	function fixCallback(& $callback)
	{
		// remove class namespace and support __invoke
		if (is_object($callback)) {
			$callback = array($callback, '__invoke');

		} elseif (is_string($callback) && $a = strrpos($callback, '\\')) {
			$callback = substr($callback, $a + 1);

		} elseif (is_array($callback) && is_string($callback[0]) && $a = strrpos($callback[0], '\\')) {
			$callback[0] = substr($callback[0], $a + 1);
		}
	}
}


/**
 * Fix for namespaced classes/interfaces in PHP < 5.3
 */
function fixNamespace(& $class)
{
	if ($a = strrpos($class, '\\')) {
		$class = substr($class, $a + 1);
	}
}
/**/