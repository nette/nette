<?php

/**
 * Test: Nette\Config\Configurator and generics.
 *
 * @author     Filip Procházka
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



interface Generic_IDao
{

}



class Dao_Impl extends Nette\Object implements  Generic_IDao
{

	public $type;

	public function __construct($type)
	{
		$this->type = $type;
		TestHelpers::note(__METHOD__ . '(' . $type . ')');
	}

}



class User extends Nette\Object
{

	public $name;

}



class Article extends Nette\Object
{

	public $title;

}



class FooModel extends Nette\Object
{

	/**
	 * @param Generic_IDao $arg <Article>
	 */
	function __construct(Generic_IDao $arg)
	{
		TestHelpers::note(__METHOD__ . '(' . $arg->type . ')');
	}

}



/**
 * @param Generic_IDao $arg <Article>
 * @param FooModel $model
 */
function fuuuu(Generic_IDao $arg, FooModel $model)
{
	TestHelpers::note(__METHOD__ . '(' . $arg->type . ')');
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.generics.neon', Configurator::NONE)
	->createContainer();

Assert::true( $container->model instanceof FooModel );

Assert::same( array(
	'Dao_Impl::__construct(Article)',
	'FooModel::__construct(Article)',
), TestHelpers::fetchNotes() );

Assert::true( $container->getService('dao', 'User') instanceof Generic_IDao );
Assert::true( $container->getService('dao', 'Article') instanceof Generic_IDao );

Assert::true($container->getService('dao<User>') instanceof Generic_IDao);
Assert::true($container->getService('dao<Article>') instanceof Generic_IDao);

Assert::same( 'User', $container->getByType('Generic_IDao<User>')->type );
Assert::same( 'Article', $container->getByType('Generic_IDao<Article>')->type );

Assert::same( $container->getService('dao', 'User'), $container->getService('dao', 'User') );
