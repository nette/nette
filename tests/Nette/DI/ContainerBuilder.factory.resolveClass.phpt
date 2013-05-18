<?php

/**
 * Test: Nette\DI\ContainerBuilder and resolving class in generated factories.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;


require __DIR__ . '/../bootstrap.php';


interface StdClassFactory
{
	function create();
}


$builder = new DI\ContainerBuilder;

$builder->addDefinition('one')
	->setImplement('StdClassFactory')
	->setClass('stdClass');

$builder->addDefinition('two')
	->setImplement('StdClassFactory')
	->setFactory('@one');

$builder->addDefinition('three')
	->setImplement('StdClassFactory')
	->setFactory('@one::create'); // alias


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type( 'StdClassFactory', $container->getService('one') );

Assert::type( 'StdClassFactory', $container->getService('two') );
Assert::type( 'StdClassFactory', $container->getService('two')->create() );
Assert::notSame( $container->getService('two')->create(), $container->getService('two')->create() );

Assert::type( 'StdClassFactory', $container->getService('three') );
Assert::type( 'stdClass', $container->getService('three')->create() );
Assert::notSame( $container->getService('three')->create(), $container->getService('three')->create() );
