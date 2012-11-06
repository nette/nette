<?php

/**
 * Test: Nette\DI\Helpers::expand()
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI\Helpers;



require __DIR__ . '/../bootstrap.php';



Assert::same( 'item', Helpers::expand('item', array()) );
Assert::same( 123, Helpers::expand(123, array()) );
Assert::same( '%', Helpers::expand('%%', array()) );
Assert::same( 'item', Helpers::expand('%key%', array('key' => 'item')) );
Assert::same( 123, Helpers::expand('%key%', array('key' => 123)) );
Assert::same( 'a123b123c', Helpers::expand('a%key%b%key%c', array('key' => 123)) );
Assert::same( 123, Helpers::expand('%key1.key2%', array('key1' => array('key2' => 123))) );
Assert::same( 123, Helpers::expand('%key1%', array('key1' => '%key2%', 'key2' => 123), TRUE) );
Assert::same( array(123), Helpers::expand(array('%key1%'), array('key1' => '%key2%', 'key2' => 123), TRUE) );
Assert::same(
	array('key1' => 123, 'key2' => 'abc'),
	Helpers::expand('%keyA%', array(
		'keyA' => array('key1' => 123, 'key2' => '%keyB%'),
		'keyB' => 'abc'
	), TRUE)
);


Assert::exception(function() {
	Helpers::expand('%missing%', array());
}, 'Nette\InvalidArgumentException', "Missing item 'missing'.");

Assert::exception(function() {
	Helpers::expand('%key1%a', array('key1' => array('key2' => 123)));
}, 'Nette\InvalidArgumentException', "Unable to concatenate non-scalar parameter 'key1' into '%key1%a'.");

Assert::exception(function() {
	Helpers::expand('%key1%', array('key1' => '%key2%', 'key2' => '%key1%'), TRUE);
}, 'Nette\InvalidArgumentException', "Circular reference detected for variables: key1, key2.");
