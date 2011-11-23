<?php

/**
 * Test: Nette\Config\Config::merge()
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../bootstrap.php';



$obj = (object) NULL;
$arr1 = array('a' => 'b', 'x');
$arr2 = array('c' => 'd', 'y');


Assert::same( NULL, Config::merge(NULL, NULL) );
Assert::same( NULL, Config::merge(NULL, 231) );
Assert::same( NULL, Config::merge(NULL, $obj) );
Assert::same( array(), Config::merge(NULL, array()) );
Assert::same( $arr1, Config::merge(NULL, $arr1) );
Assert::same( 231, Config::merge(231, NULL) );
Assert::same( 231, Config::merge(231, 231) );
Assert::same( 231, Config::merge(231, $obj) );
Assert::same( 231, Config::merge(231, array()) );
Assert::same( 231, Config::merge(231, $arr1) );
Assert::same( $obj, Config::merge($obj, NULL) );
Assert::same( $obj, Config::merge($obj, 231) );
Assert::same( $obj, Config::merge($obj, $obj) );
Assert::same( $obj, Config::merge($obj, array()) );
Assert::same( $obj, Config::merge($obj, $arr1) );
Assert::same( array(), Config::merge(array(), NULL) );
Assert::same( array(), Config::merge(array(), 231) );
Assert::same( array(), Config::merge(array(), $obj) );
Assert::same( array(), Config::merge(array(), array()) );
Assert::same( $arr1, Config::merge(array(), $arr1) );
Assert::same( $arr2, Config::merge($arr2, NULL) );
Assert::same( $arr2, Config::merge($arr2, 231) );
Assert::same( $arr2, Config::merge($arr2, $obj) );
Assert::same( $arr2, Config::merge($arr2, array()) );
Assert::same( array('a' => 'b', 'x', 'c' => 'd', 'y'), Config::merge($arr2, $arr1) );
