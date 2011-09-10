<?php

/**
 * Test: Nette\ArrayList basic usage.
 *
 * @author     David Grudl
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ArrayList;



require __DIR__ . '/../bootstrap.php';



class Person
{
	private $name;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function sayHi()
	{
		return "My name is $this->name";
	}

}



$list = new ArrayList;
$jack = new Person('Jack');
$mary = new Person('Mary');

$list[] = $mary;
$list[] = $jack;

Assert::same( $mary, $list[0] );
Assert::same( $jack, $list[1] );



Assert::same( array(
	$mary,
	$jack,
), iterator_to_array($list) );


foreach ($list as $key => $person) {
	$tmp[] = $key . ' => ' . $person->sayHi();
}
Assert::same( array(
	'0 => My name is Mary',
	'1 => My name is Jack',
), $tmp );



Assert::same( 2, $list->count(), 'count:' );
Assert::same( 2, count($list) );



Assert::throws(function() use ($list) {
	unset($list[-1]);
}, 'OutOfRangeException', 'Offset invalid or out of range');

Assert::throws(function() use ($list) {
	unset($list[2]);
}, 'OutOfRangeException', 'Offset invalid or out of range');

unset($list[1]);
Assert::same( array(
	$mary,
), iterator_to_array($list) );
