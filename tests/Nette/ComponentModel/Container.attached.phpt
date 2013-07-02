<?php

/**
 * Test: Nette\ComponentModel\Container::attached()
 *
 * @author     David Grudl
 * @package    Nette\ComponentModel
 */

use Nette\ComponentModel\Container;


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


class A extends TestClass {}
class B extends TestClass {}
class C extends TestClass {}
class D extends TestClass {}
class E extends TestClass {}

$d = new D;
$d['e'] = new E;
$b = new B;
$b->monitor('a');
$b['c'] = new C;
$b['c']->monitor('a');
$b['c']['d'] = $d;

// 'a' becoming 'b' parent
$a = new A;
$a['b'] = $b;
Assert::same( array(
	'C::ATTACHED(A)',
	'B::ATTACHED(A)',
), Notes::fetch());


// removing 'b' from 'a'
unset($a['b']);
Assert::same( array(
	'C::detached(A)',
	'B::detached(A)',
), Notes::fetch());

// 'a' becoming 'b' parent
$a['b'] = $b;

Assert::same( 'b-c-d-e', $d['e']->lookupPath('A') );
Assert::same( $a, $d['e']->lookup('A') );
Assert::same( 'b-c-d-e', $d['e']->lookupPath(NULL) );
Assert::same( $a, $d['e']->lookup(NULL) );
Assert::same( 'c-d-e', $d['e']->lookupPath('B') );
Assert::same( $b, $d['e']->lookup('B') );

Assert::same( $a['b-c'], $b['c'] );
