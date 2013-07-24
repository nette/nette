<?php

/**
 * Test: Nette\DI\Compiler: generated services factories.
 *
 * @author     Filip Prochazka
 * @package    Nette\DI
 */

use Nette\DI;


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

	public function __construct(Bar $bar, Baz $baz)
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
	public function create(Baz $baz);
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.generatedFactory.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'ILoremFactory', $container->getService('lorem') );
$lorem = $container->getService('lorem')->create();
Assert::type( 'Lorem', $lorem );
Assert::type( 'Ipsum', $lorem->ipsum );
Assert::same( $container->getService('ipsum'), $lorem->ipsum );


Assert::type( 'IArticleFactory', $container->getService('article') );
$article = $container->getService('article')->create('nemam');
Assert::type( 'Article', $article );
Assert::same( 'nemam', $article->title );


Assert::type( 'IFooFactory', $container->getService('foo') );
$foo = $container->getService('foo')->create($container->getService('baz'));
Assert::type( 'Foo', $foo );
Assert::type( 'Bar', $foo->bar );
Assert::same($container->getService('bar'), $foo->bar);
Assert::type( 'Baz', $foo->baz );
Assert::same($container->getService('baz'), $foo->baz);

Assert::type( 'ILoremFactory', $container->getByType('ILoremFactory') );
