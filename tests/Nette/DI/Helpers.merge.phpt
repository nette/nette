<?php

/**
 * Test: Nette\DI\Config\Helpers::merge()
 *
 * @author     David Grudl
 */

use Nette\DI\Config\Helpers,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$obj = new stdClass;
$arr1 = array('a' => 'b', 'x');
$arr2 = array('c' => 'd', 'y');


Assert::same( NULL, Helpers::merge(NULL, NULL) );
Assert::same( NULL, Helpers::merge(NULL, 231) );
Assert::same( NULL, Helpers::merge(NULL, $obj) );
Assert::same( array(), Helpers::merge(NULL, array()) );
Assert::same( $arr1, Helpers::merge(NULL, $arr1) );
Assert::same( 231, Helpers::merge(231, NULL) );
Assert::same( 231, Helpers::merge(231, 231) );
Assert::same( 231, Helpers::merge(231, $obj) );
Assert::same( 231, Helpers::merge(231, array()) );
Assert::same( 231, Helpers::merge(231, $arr1) );
Assert::same( $obj, Helpers::merge($obj, NULL) );
Assert::same( $obj, Helpers::merge($obj, 231) );
Assert::same( $obj, Helpers::merge($obj, $obj) );
Assert::same( $obj, Helpers::merge($obj, array()) );
Assert::same( $obj, Helpers::merge($obj, $arr1) );
Assert::same( array(), Helpers::merge(array(), NULL) );
Assert::same( array(), Helpers::merge(array(), 231) );
Assert::same( array(), Helpers::merge(array(), $obj) );
Assert::same( array(), Helpers::merge(array(), array()) );
Assert::same( $arr1, Helpers::merge(array(), $arr1) );
Assert::same( $arr2, Helpers::merge($arr2, NULL) );
Assert::same( $arr2, Helpers::merge($arr2, 231) );
Assert::same( $arr2, Helpers::merge($arr2, $obj) );
Assert::same( $arr2, Helpers::merge($arr2, array()) );
Assert::same( array('a' => 'b', 'x', 'c' => 'd', 'y'), Helpers::merge($arr2, $arr1) );
