<?php

/**
 * Test: Nette\ComponentContainer::attached()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\ComponentContainer;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



class TestClass extends ComponentContainer implements ArrayAccess
{
	protected function attached($obj)
	{
		output(get_class($this) . '::ATTACHED(' . get_class($obj) . ')');
	}

	protected function detached($obj)
	{
		output(get_class($this) . '::detached(' . get_class($obj) . ')');
	}

	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}

	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}

	final public function offsetExists($name)
	{
		return $this->getComponent($name) !== NULL;
	}

	final public function offsetUnset($name)
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

output("'a' becoming 'b' parent");
$a = new A;
$a['b'] = $b;

output("removing 'b' from 'a'");
unset($a['b']);

output("'a' becoming 'b' parent");
$a['b'] = $b;

dump( $d['e']->lookupPath('A'), "'e' looking 'a'" );

dump( $a['b-c'] === $b['c'], "checking 'a-b-c'" );



__halt_compiler() ?>

------EXPECT------
'a' becoming 'b' parent

C::ATTACHED(A)

B::ATTACHED(A)

removing 'b' from 'a'

C::detached(A)

B::detached(A)

'a' becoming 'b' parent

C::ATTACHED(A)

B::ATTACHED(A)

'e' looking 'a': string(7) "b-c-d-e"

checking 'a-b-c': bool(TRUE)
