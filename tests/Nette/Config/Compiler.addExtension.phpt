<?php

/**
 * Test: Nette\Config\Compiler and addExtension on loadConfiguration stage.
 *
 * @author     Josef Kříž
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Compiler;
use Nette\Config\CompilerExtension;



require __DIR__ . '/../bootstrap.php';



class BaseExtension extends CompilerExtension
{

	public $loaded = false;


	public function loadConfiguration()
	{
		$this->loaded = true;
	}
}


class FooExtension extends BaseExtension
{

	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$this->compiler->addExtension('bar', new BarExtension);
	}
}


class BarExtension extends BaseExtension
{

}


$compiler = new Compiler;

// hack for private config
$ref = new ReflectionClass(get_class($compiler));
$property = $ref->getProperty('config');
$property->setAccessible(TRUE);
$property->setValue($compiler, array());

$compiler->addExtension('foo', new FooExtension());
$extensions = $compiler->getExtensions();

Assert::same(1, count($extensions));
Assert::false($extensions['foo']->loaded);


// first running
$compiler->processExtensions();
$extensions = $compiler->getExtensions();

Assert::same(2, count($extensions));
Assert::true($extensions['foo']->loaded);
Assert::true($extensions['bar']->loaded);


// second running
$extensions['foo']->loaded = false;
$extensions['bar']->loaded = false;
$compiler->processExtensions();
$extensions = $compiler->getExtensions();

Assert::same(2, count($extensions));
Assert::true($extensions['foo']->loaded);
Assert::true($extensions['bar']->loaded);
