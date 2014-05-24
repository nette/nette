<?php

/**
 * Test: Nette\Reflection\Extension tests.
 */

use Nette\Reflection,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$ext = new Reflection\Extension('standard');
$funcs = $ext->getFunctions();
Assert::equal( new Reflection\GlobalFunction('sleep'), $funcs['sleep'] );


if (!class_exists('PDO')) {
	Tester\Environment::skip('For full test requires PHP extension PDO.');
}


$ext = new Reflection\Extension('pdo');
Assert::equal( array(
	'PDOException' => new Reflection\ClassType('PDOException'),
	'PDO' => new Reflection\ClassType('PDO'),
	'PDOStatement' => new Reflection\ClassType('PDOStatement'),
	'PDORow' => new Reflection\ClassType('PDORow'),
), $ext->getClasses() );
