<h1>Nette\Component cloning test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\ComponentContainer;*/
/*use Nette\Debug;*/

Debug::enable();
Debug::$maxDepth = 0;

class Test extends ComponentContainer implements /*\*/ArrayAccess
{
	protected function attached($obj)
	{
		echo get_class($this) . "::ATTACHED(" . get_class($obj) . ")\n";
	}

	protected function detached($obj)
	{
		echo get_class($this) . "::detached(" . get_class($obj) . ")\n";
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


/*Nette\Object\extensionMethod('Nette\IComponentContainer\export', function($thisObj)*/
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


class A extends Test {}
class B extends Test {}
class C extends Test {}
class D extends Test {}
class E extends Test {}

$a = new A;
$a['b'] = new B;
$a['b']['c'] = new C;
$a['b']['c']['d'] = new D;
$a['b']['c']['d']['e'] = new E;

$a['b']->monitor('a');
$a['b']['c']->monitor('a');

echo "\n'e' looking 'a'\n";
echo $a['b']['c']['d']['e']->lookupPath('A', FALSE), "\n";

echo "\nclone 'c'\n";
$dolly = clone $a['b']['c'];

echo "\n'e' looking 'a'\n";
echo $dolly['d']['e']->lookupPath('A', FALSE), "\n";

echo "\n'e' looking 'c'\n";
echo $dolly['d']['e']->lookupPath('C', FALSE), "\n";

echo "\nclone 'b'\n";
$dolly = clone $a['b'];

echo "\na['dolly'] = 'b'\n";
$a['dolly'] = $dolly;

echo "\nexport 'a'\n";
Debug::dump($a->export());
