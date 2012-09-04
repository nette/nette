<?php

/**
 * Test: Nette\Reflection\Parameter default values test.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



function test($method, $args)
{
	$method = new Reflection\Method($method);
	foreach ($method->getParameters() as $param) {
		echo "{$method->getName()}(\${$param->getName()})\n";
		@list($isOptional, $isDefaultValueAvailable, $defaultValue) = array_shift($args);
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
	function func6($a, PDO $b = NULL, PDO $c) {}
	function func7($a, $b = NULL, PDO $c = NULL) {}
}


test( 'Test::func1', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, FALSE),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func2', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func3', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'Test::func4', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func5', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'Test::func6', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(FALSE, PHP_VERSION_ID >= 50407),
	/* $c */ array(FALSE, FALSE)
));
test( 'Test::func7', array(
	/* $a */ array(FALSE, FALSE),
	/* $b */ array(TRUE, TRUE, NULL),
	/* $c */ array(TRUE, TRUE, NULL)
));
test( 'PDO::__construct', array(
	/* $dsn */ array(FALSE, FALSE),
	/* $username */ array(FALSE, FALSE),
	/* $passwd */ array(FALSE, FALSE),
	/* $options */ array(TRUE, FALSE)
));
