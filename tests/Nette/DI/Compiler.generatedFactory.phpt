<?php

/**
 * Test: Nette\DI\Compiler: generated services factories.
 *
 * @author     Filip Prochazka
 * @package    Nette\DI
 */

use Nette\DI;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


interface ILoremFactory
{

	/**
	 * @return Lorem
	 */
	function create();
}

class Lorem
{

	public $ipsum;

	function __construct(Ipsum $ipsum)
	{
		$this->ipsum = $ipsum;
	}

}

interface IArticleFactory
{

	/**
	 * @param string
	 * @return Article
	 */
	function create($title);
}

class Article
{
	public $title;

	function __construct($title)
	{
		$this->title = $title;
	}
}

class Ipsum
{

}

class Foo
{
	public $bar;
	public $baz;

	public function __construct(Bar $bar, Baz $baz = NULL)
	{
		$this->bar = $bar;
		$this->baz = $baz;
	}
}

class Bar
{

}

class Baz
{

}

interface IFooFactory
{
	/**
	 * @param Baz
	 * @return Foo
	 */
	public function create(Baz $baz = NULL);
}

class TestExtension extends DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition('fooFactory2')
			->setFactory('Foo')
			->setParameters(array('Baz baz'))
			->setImplement('IFooFactory')
			->setArguments(array($builder::literal('$baz')));

		// needed order: parameters, implement because of setting shared = true
		// see definition by config in Compiler::parseService()
	}
}

$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$compiler->addExtension('test', new TestExtension);
$code = $compiler->compile($loader->load('files/compiler.generatedFactory.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'ILoremFactory', $container->getService('lorem') );
$lorem = $container->getService('lorem')->create();
Assert::type( 'Lorem', $lorem );
Assert::type( 'Ipsum', $lorem->ipsum );
Assert::same( $container->getService('ipsum'), $lorem->ipsum );

Assert::type( 'ILoremFactory', $container->getByType('ILoremFactory') );


Assert::type( 'IArticleFactory', $container->getService('article') );
$article = $container->getService('article')->create('nemam');
Assert::type( 'Article', $article );
Assert::same( 'nemam', $article->title );


Assert::type( 'IFooFactory', $container->getService('fooFactory1') );
$foo = $container->getService('fooFactory1')->create($container->getService('baz'));
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::type( 'Baz', $foo->baz );
Assert::same($container->getService('baz'), $foo->baz);
$foo = $container->getService('fooFactory1')->create();
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::null( $foo->baz );


Assert::type( 'IFooFactory', $container->getService('fooFactory2') );
$foo = $container->getService('fooFactory2')->create($container->getService('baz'));
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::type( 'Baz', $foo->baz );
Assert::same($container->getService('baz'), $foo->baz);
$foo = $container->getService('fooFactory2')->create();
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::null( $foo->baz );


Assert::type( 'IFooFactory', $container->getService('fooFactory3') );
$foo = $container->getService('fooFactory3')->create($container->getService('baz'));
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::type( 'Baz', $foo->baz );
Assert::same($container->getService('baz'), $foo->baz);
$foo = $container->getService('fooFactory3')->create();
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::null( $foo->baz );
