<?php

/**
 * Test: Expanding class alias to FQN.
 */

use Nette\Reflection\AnnotationsParser,
	Tester\Assert;

require __DIR__ . '/../bootstrap.php';


require __DIR__ . '/files/expandClass.noNamespace.php';
require __DIR__ . '/files/expandClass.inNamespace.php';

$rcTest = new \ReflectionClass('Test');
$rcFoo = new \ReflectionClass('Test\Space\Foo');
$rcBar = new \ReflectionClass('Test\Space\Bar');


Assert::exception( function() use ($rcTest) {
	AnnotationsParser::expandClassName('', $rcTest);
}, 'Nette\InvalidArgumentException', 'Class name must not be empty.' );


Assert::same( 'A', AnnotationsParser::expandClassName('A', $rcTest) );
Assert::same( 'A\B', AnnotationsParser::expandClassName('C', $rcTest) );


/*
alias to expand => array(
	FQN for $rcFoo,
	FQN for $rcBar
)
*/
$cases = array(
	'\Absolute' => array(
		'Absolute',
		'Absolute'
	),
	'\Absolute\Foo' => array(
		'Absolute\Foo',
		'Absolute\Foo'
	),

	'AAA' => array(
		'Test\Space\AAA',
		'AAA'
	),
	'AAA\Foo' => array(
		'Test\Space\AAA\Foo',
		'AAA\Foo'
	),

	'B' => array(
		'Test\Space\B',
		'BBB'
	),
	'B\Foo' => array(
		'Test\Space\B\Foo',
		'BBB\Foo'
	),

	'DDD' => array(
		'Test\Space\DDD',
		'CCC\DDD'
	),
	'DDD\Foo' => array(
		'Test\Space\DDD\Foo',
		'CCC\DDD\Foo'
	),

	'F' => array(
		'Test\Space\F',
		'EEE\FFF'
	),
	'F\Foo' => array(
		'Test\Space\F\Foo',
		'EEE\FFF\Foo'
	),

	'HHH' => array(
		'Test\Space\HHH',
		'Test\Space\HHH'
	),

	'Notdef' => array(
		'Test\Space\Notdef',
		'Test\Space\Notdef'
	),
	'Notdef\Foo' => array(
		'Test\Space\Notdef\Foo',
		'Test\Space\Notdef\Foo'
	),

	// case insensivity
	'aAa' => array(
		'Test\Space\aAa',
		'AAA'
	),
	'AaA\Foo' => array(
		'Test\Space\AaA\Foo',
		'AAA\Foo'
	),

	// trim leading backslash
	'G' => array(
		'Test\Space\G',
		'GGG'
	),
	'G\Foo' => array(
		'Test\Space\G\Foo',
		'GGG\Foo'
	),
);
foreach ($cases as $alias => $fqn) {
	Assert::same( $fqn[0], AnnotationsParser::expandClassName($alias, $rcFoo) );
	Assert::same( $fqn[1], AnnotationsParser::expandClassName($alias, $rcBar) );
}
