<?php

/**
 * Test: Nette\ComponentContainer cloning.
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


/*Nette\Object::extensionMethod('Nette\IComponentContainer::export', function($thisObj)*/
/**/function IComponentContainer_prototype_export($thisObj)/**/
{
	$res = array("($thisObj->class)" => $thisObj->name);
	if ($thisObj instanceof /*Nette\*/IComponentContainer) {
		foreach ($thisObj->getComponents() as $name => $obj) {
			$res['children'][$name] = $obj->export();
		}
	}
	return $res;
}/*);*/


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

dump( $a['b']['c']['d']['e']->lookupPath('A', FALSE), "'e' looking 'a'" );

output("==> clone 'c'");
$dolly = clone $a['b']['c'];

dump( $dolly['d']['e']->lookupPath('A', FALSE), "'e' looking 'a'" );

dump( $dolly['d']['e']->lookupPath('C', FALSE), "'e' looking 'c'" );

output("==> clone 'b'");
$dolly = clone $a['b'];

output("==> a['dolly'] = 'b'");
$a['dolly'] = $dolly;

dump( $a->export(), "export 'a'" );



__halt_compiler();

------EXPECT------
B::ATTACHED(A)

C::ATTACHED(A)

'e' looking 'a': string(7) "b-c-d-e"

==> clone 'c'

C::detached(A)

'e' looking 'a': NULL

'e' looking 'c': string(3) "d-e"

==> clone 'b'

C::detached(A)

B::detached(A)

==> a['dolly'] = 'b'

C::ATTACHED(A)

B::ATTACHED(A)

export 'a': array(2) {
	"(A)" => NULL
	"children" => array(2) {
		"b" => array(2) {
			"(B)" => string(1) "b"
			"children" => array(1) {
				"c" => array(2) {
					"(C)" => string(1) "c"
					"children" => array(1) {
						...
					}
				}
			}
		}
		"dolly" => array(2) {
			"(B)" => string(5) "dolly"
			"children" => array(1) {
				"c" => array(2) {
					"(C)" => string(1) "c"
					"children" => array(1) {
						...
					}
				}
			}
		}
	}
}
