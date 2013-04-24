<?php

/**
 * Test: Nette\Config\Compiler: generated services factories.
 *
 * @author     Filip Prochazka
 * @package    Nette\Config
 */

use Nette\Config;



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




$loader = new Config\Loader;
$compiler = new Config\Compiler;
$code = $compiler->compile($loader->load('files/compiler.generatedFactory.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('lorem') instanceof ILoremFactory );
$lorem = $container->getService('lorem')->create();
Assert::true( $lorem instanceof Lorem );
Assert::true( $lorem->ipsum instanceof Ipsum );
Assert::same( $container->getService('ipsum'), $lorem->ipsum );


Assert::true( $container->getService('article') instanceof IArticleFactory );
$article = $container->getService('article')->create('nemam');
Assert::true( $article instanceof Article );
Assert::same( 'nemam', $article->title );


Assert::true($container->getService('foo') instanceof IFooFactory);
$foo = $container->getService('foo')->create($container->getService('baz'));
Assert::true($foo instanceof Foo);
Assert::true($foo->bar instanceof Bar);
Assert::same($container->getService('bar'), $foo->bar);
Assert::true($foo->baz instanceof Baz);
Assert::same($container->getService('baz'), $foo->baz);

Assert::true($container->getByType('ILoremFactory') instanceof ILoremFactory);
