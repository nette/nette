<?php

/**
 * Test: Nette\Reflection\Parameter default values test.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


function test($method, $args)
{
	$method = new Reflection\Method($method);
	foreach ($method->getParameters() as $param) {
		echo "{$method->getName()}(\${$param->getName()})\n";
		list($isOptional, $isDefaultValueAvailable, $defaultValue) = array_shift($args) + array(NULL, NULL, NULL);
		Assert::same( $isOptional, $param->isOptional() );
		Assert::same( $isDefaultValueAvailable, $param->isDefaultValueAvailable() );

		if ($isDefaultValueAvailable) {
			Assert::same( $defaultValue, $param->getDefaultValue() );
		}
	}
}


class Test
{
	function func1($a, $b, $c) {}
	function func2($a, $b = NULL, $c) {}
	function func3($a, $b = NULL, $c = NULL) {}
	function func4($a, array $b = NULL, array $c) {}
	function func5($a, $b = NULL, array $c = NULL) {}
	function func6($a, Exception $b = NULL, Exception $c) {}
	function func7($a, $b = NULL, Exception $c = NULL) {}
}


test( 'Test::func1', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, FALSE),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func2', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407 || (PHP_VERSION_ID >= 50317 && PHP_VERSION_ID < 50400)),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func3', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'Test::func4', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407 || (PHP_VERSION_ID >= 50317 && PHP_VERSION_ID < 50400)),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func5', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'Test::func6', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407 || (PHP_VERSION_ID >= 50317 && PHP_VERSION_ID < 50400)),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func7', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'Exception::__construct', array(
	/* $message */ array(TRUE, FALSE),
	/* $code */ array(TRUE, FALSE),
	/* $previous */ array(TRUE, FALSE),
));
test( 'FilesystemIterator::__construct', array(
	/* $path */ array(FALSE, FALSE),
	/* $flags */ array(TRUE, FALSE),
));
