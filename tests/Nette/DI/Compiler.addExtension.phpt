<?php

/**
 * Test: Nette\DI\Compiler and addExtension on loadConfiguration stage.
 *
 * @author     Josef Kříž
 * @package    Nette\DI
 */

use Nette\DI\CompilerExtension;


require __DIR__ . '/../bootstrap.php';


abstract class BaseExtension extends CompilerExtension
{
	public $loaded = false;

	public function loadConfiguration()
	{
		Notes::add(get_class($this));
		$this->loaded = true;
	}
}

class FooExtension extends BaseExtension
{
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$this->compiler->addExtension('bar', new BarExtension);

		foreach ($this->compiler->getExtensions() as $extension) {
			// iterating over array breaks the cursor
		}
	}
}

class BarExtension extends BaseExtension
{

}

class BazExtension extends BaseExtension
{

}

class ProcessingCompiler extends Nette\DI\Compiler
{
	public function generateCode($className, $parentName)
	{
		return NULL;
	}
}


$compiler = new ProcessingCompiler;

$compiler->addExtension('foo', new FooExtension());
$compiler->addExtension('baz', new BazExtension());
$extensions = $compiler->getExtensions();

Assert::same(2, count($extensions));
Assert::false($extensions['foo']->loaded);
Assert::false($extensions['baz']->loaded);


// first running
$compiler->compile(array(), 'SystemContainer', 'Nette\DI\Container');
$extensions = $compiler->getExtensions();

Assert::same(3, count($extensions));
Assert::true($extensions['foo']->loaded);
Assert::true($extensions['bar']->loaded);
Assert::true($extensions['baz']->loaded);

Assert::same( array('FooExtension', 'BazExtension', 'BarExtension'), Notes::fetch() );


// second running
$extensions['foo']->loaded = false;
$extensions['bar']->loaded = false;
$extensions['baz']->loaded = false;
$compiler->compile(array(), 'SystemContainer', 'Nette\DI\Container');
$extensions = $compiler->getExtensions();

Assert::same(3, count($extensions));
Assert::true($extensions['foo']->loaded);
Assert::true($extensions['bar']->loaded);
Assert::true($extensions['baz']->loaded);

Assert::same(array('FooExtension', 'BazExtension', 'BarExtension'), Notes::fetch());
