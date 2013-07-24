<?php

/**
 * Test: Nette\ComponentModel\Container cloning.
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 */

use Nette\ComponentModel\Container,
	Nette\Object,
	Nette\ComponentModel\IContainer;


require __DIR__ . '/../bootstrap.php';


class TestClass extends Container implements ArrayAccess
{
	function attached($obj)
	{
		Notes::add(get_class($this) . '::ATTACHED(' . get_class($obj) . ')');
	}

	function detached($obj)
	{
		Notes::add(get_class($this) . '::detached(' . get_class($obj) . ')');
	}

	function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}

	function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}

	function offsetExists($name)
	{
		return $this->getComponent($name) !== NULL;
	}

	function offsetUnset($name)
	{
		$this->removeComponent($this->getComponent($name, TRUE));
	}
}


Object::extensionMethod('Nette\\ComponentModel\\IContainer::export', function($thisObj) {
	$res = array("({$thisObj->reflection->name})" => $thisObj->name);
	if ($thisObj instanceof IContainer) {
		foreach ($thisObj->getComponents() as $name => $obj) {
			$res['children'][$name] = $obj->export();
		}
	}
	return $res;
});


class A extends TestClass {}
class B extends TestClass {}
class C extends TestClass {}
class D extends TestClass {}
class E extends TestClass {}

$a = new A;
$a['b'] = new B;
$a['b']['c'] = new C;
$a['b']['c']['d'] = new D;
$a['b']['c']['d']['e'] = new E;

$a['b']->monitor('a');
$a['b']->monitor('a');
$a['b']['c']->monitor('a');

Assert::same( array(
	'B::ATTACHED(A)',
	'C::ATTACHED(A)',
), Notes::fetch());

Assert::same( 'b-c-d-e', $a['b']['c']['d']['e']->lookupPath('A', FALSE) );


// ==> clone 'c'
$dolly = clone $a['b']['c'];

Assert::same( array(
	'C::detached(A)',
), Notes::fetch());

Assert::null( $dolly['d']['e']->lookupPath('A', FALSE) );

Assert::same( 'd-e', $dolly['d']['e']->lookupPath('C', FALSE) );


// ==> clone 'b'
$dolly = clone $a['b'];

Assert::same( array(
	'C::detached(A)',
	'B::detached(A)',
), Notes::fetch());


// ==> a['dolly'] = 'b'
$a['dolly'] = $dolly;

Assert::same( array(
	'C::ATTACHED(A)',
	'B::ATTACHED(A)',
), Notes::fetch());

Assert::same( array(
	'(A)' => NULL,
	'children' => array(
		'b' => array(
			'(B)' => 'b',
			'children' => array(
				'c' => array(
					'(C)' => 'c',
					'children' => array(
						'd' => array(
							'(D)' => 'd',
							'children' => array(
								'e' => array(
									'(E)' => 'e',
								),
							),
						),
					),
				),
			),
		),
		'dolly' => array(
			'(B)' => 'dolly',
			'children' => array(
				'c' => array(
					'(C)' => 'c',
					'children' => array(
						'd' => array(
							'(D)' => 'd',
							'children' => array(
								'e' => array(
									'(E)' => 'e',
								),
							),
						),
					),
				),
			),
		),
	),
), $a->export() );
