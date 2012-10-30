<?php

/**
 * Test: Nette\Config\Configurator: generated services factories.
 *
 * @author     Filip Prochazka
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



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


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.generatedFactory.neon')
	->createContainer();

Assert::true( $container->lorem instanceof ILoremFactory );
$lorem = $container->lorem->create();
Assert::true( $lorem instanceof Lorem );
Assert::true( $lorem->ipsum instanceof Ipsum );
Assert::same( $container->ipsum, $lorem->ipsum );


Assert::true( $container->article instanceof IArticleFactory );
$article = $container->article->create('nemam');
Assert::true( $article instanceof Article );
Assert::same( 'nemam', $article->title );


Assert::true($container->foo instanceof IFooFactory);
$foo = $container->foo->create($container->baz);
Assert::true($foo instanceof Foo);
Assert::true($foo->bar instanceof Bar);
Assert::same($container->bar, $foo->bar);
Assert::true($foo->baz instanceof Baz);
Assert::same($container->baz, $foo->baz);
