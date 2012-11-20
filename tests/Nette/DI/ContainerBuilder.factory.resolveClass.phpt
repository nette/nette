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

Assert::true( $container->getService('one') instanceof StdClassFactory );

Assert::true( $container->getService('two') instanceof StdClassFactory );
Assert::true( $container->getService('two')->create() instanceof StdClassFactory );

Assert::true( $container->getService('three') instanceof StdClassFactory );
Assert::true( $container->getService('three')->create() instanceof stdClass );
