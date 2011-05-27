<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroForeach()
 *
 * @author     Matej Kravjar
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new DefaultMacros;
$parser = new Nette\Latte\Parser;
$macros->initialize($parser);
$prefix = '$iterator = $_l->its[] = new Nette\Iterators\CachingIterator(';

Assert::same( $prefix . '$array) as $value',  $macros->macroForeach('$array as $value') );
Assert::same( $prefix . '$array) as $key => $value',  $macros->macroForeach('$array as $key => $value') );

Assert::same( $prefix . '$obj->data("A as B")) as $value',  $macros->macroForeach('$obj->data("A as B") as $value') );
Assert::same( $prefix . '$obj->data(\'A as B\')) as $value',  $macros->macroForeach('$obj->data(\'A as B\') as $value') );
Assert::same( $prefix . '$obj->data("X as Y, Z as W")) as $value',  $macros->macroForeach('$obj->data("X as Y, Z as W") as $value') );
