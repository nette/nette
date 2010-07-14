<?php

/**
 * Test: Nette\ComponentContainer cloning.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../initialize.php';



class TestClass extends ComponentContainer implements ArrayAccess
{
	protected function attached($obj)
	{
		T::note(get_class($this) . '::ATTACHED(' . get_class($obj) . ')');
	}

	protected function detached($obj)
	{
		T::note(get_class($this) . '::detached(' . get_class($obj) . ')');
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


/**/Nette\Object::extensionMethod('Nette\\IComponentContainer::export', function($thisObj)/**/
/*5.2* function IComponentContainer_prototype_export($thisObj)*/
{
	$res = array("({$thisObj->reflection->name})" => $thisObj->name);
	if ($thisObj instanceof Nette\IComponentContainer) {
		foreach ($thisObj->getComponents() as $name => $obj) {
			$res['children'][$name] = $obj->export();
		}
	}
	return $res;
}/**/);/**/


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

T::dump( $a['b']['c']['d']['e']->lookupPath('A', FALSE), "'e' looking 'a'" );

T::note("==> clone 'c'");
$dolly = clone $a['b']['c'];

T::dump( $dolly['d']['e']->lookupPath('A', FALSE), "'e' looking 'a'" );

T::dump( $dolly['d']['e']->lookupPath('C', FALSE), "'e' looking 'c'" );

T::note("==> clone 'b'");
$dolly = clone $a['b'];

T::note("==> a['dolly'] = 'b'");
$a['dolly'] = $dolly;

T::dump( $a->export(), "export 'a'" );



__halt_compiler() ?>

------EXPECT------
B::ATTACHED(A)

C::ATTACHED(A)

'e' looking 'a': "b-c-d-e"

==> clone 'c'

C::detached(A)

'e' looking 'a': NULL

'e' looking 'c': "d-e"

==> clone 'b'

C::detached(A)

B::detached(A)

==> a['dolly'] = 'b'

C::ATTACHED(A)

B::ATTACHED(A)

export 'a': array(
	"(A)" => NULL
	"children" => array(
		"b" => array(
			"(B)" => "b"
			"children" => array(
				"c" => array(
					"(C)" => "c"
					"children" => array( ... )
				)
			)
		)
		"dolly" => array(
			"(B)" => "dolly"
			"children" => array(
				"c" => array(
					"(C)" => "c"
					"children" => array( ... )
				)
			)
		)
	)
)
