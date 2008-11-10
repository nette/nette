<h1>Nette\Component attaching/detaching test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\ComponentContainer;*/
/*use Nette\Debug;*/

Debug::enable();

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


class A extends Test {}
class B extends Test {}
class C extends Test {}
class D extends Test {}
class E extends Test {}

$d = new D;
$d['e'] = new E;
$b = new B;
$b->monitor('a');
$b['c'] = new C;
$b['c']->monitor('a');
$b['c']['d'] = $d;

echo "\n'a' becoming 'b' parent\n";
$a = new A;
$a['b'] = $b;

echo "\nremoving 'b' from 'a'\n";
unset($a['b']);

echo "\n'a' becoming 'b' parent\n";
$a['b'] = $b;

echo "\n'e' looking 'a'\n";
echo $d['e']->lookupPath('A');