<?php

/**
 * Test: Nette\Templates\LatteMacros::macroForeach()
 *
 * @author     Matej Kravjar
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new LatteMacros;
$prefix = '$iterator = $_l->its[] = new Nette\SmartCachingIterator(';

Assert::same( $prefix . '$array) as $value',  $macros->macroForeach('$array as $value') );
Assert::same( $prefix . '$array) as $key => $value',  $macros->macroForeach('$array as $key => $value') );

Assert::same( $prefix . '$obj->data("A as B")) as $value',  $macros->macroForeach('$obj->data("A as B") as $value') );
Assert::same( $prefix . '$obj->data(\'A as B\')) as $value',  $macros->macroForeach('$obj->data(\'A as B\') as $value') );
Assert::same( $prefix . '$obj->data("X as Y, Z as W")) as $value',  $macros->macroForeach('$obj->data("X as Y, Z as W") as $value') );
