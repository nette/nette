<?php

/**
 * Test: Nette\Reflection\Extension tests.
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection;


require __DIR__ . '/../bootstrap.php';


$ext = new Reflection\Extension('standard');
$funcs = $ext->getFunctions();
Assert::equal( new Reflection\GlobalFunction('sleep'), $funcs['sleep'] );


$ext = new Reflection\Extension('pdo');
Assert::equal( array(
	'PDOException' => new Reflection\ClassType('PDOException'),
	'PDO' => new Reflection\ClassType('PDO'),
	'PDOStatement' => new Reflection\ClassType('PDOStatement'),
	'PDORow' => new Reflection\ClassType('PDORow'),
), $ext->getClasses() );
