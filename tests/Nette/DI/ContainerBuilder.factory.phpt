<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories.
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

interface AnnotatedFactory
{
	/** @return stdClass */
	function create();
}

class FactoryReceiver
{
	function __construct(StdClassFactory $factory)
	{}
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setImplement('stdClassFactory')
	->setFactory('stdClass');

$builder->addDefinition('two')
	->setImplement('AnnotatedFactory');

$builder->addDefinition('three')
	->setClass('FactoryReceiver');

$builder->addDefinition('four')
	->setFactory('FactoryReceiver', array('@one'));


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->getService('one') instanceof StdClassFactory );
Assert::true( $container->getService('one')->create() instanceof stdClass );
Assert::false( $container->getService('one')->create() === $container->getService('one')->create() );

Assert::true( $container->getService('two') instanceof AnnotatedFactory );
Assert::true( $container->getService('two')->create() instanceof stdClass );
Assert::false( $container->getService('two')->create() === $container->getService('two')->create() );

Assert::true( $container->getService('three') instanceof FactoryReceiver );

Assert::true( $container->getService('four') instanceof FactoryReceiver );
