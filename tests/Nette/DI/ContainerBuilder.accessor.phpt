<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated accessors.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


interface StdClassAccessor
{
	function get();
}

interface AnnotatedAccessor
{
	/** @return stdClass */
	function get();
}

class AccessorReceiver
{
	public $accessor;

	function __construct(StdClassAccessor $accessor)
	{
		$this->accessor = $accessor;
	}
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('service')
	->setFactory('stdClass');

$builder->addDefinition('service2')
	->setAutowired(FALSE)
	->setFactory('stdClass');

$builder->addDefinition('one')
	->setImplement('stdClassAccessor')
	->setClass('stdClass');

$builder->addDefinition('two')
	->setImplement('AnnotatedAccessor');

$builder->addDefinition('three')
	->setImplement('stdClassAccessor')
	->setAutowired(FALSE)
	->setFactory('@service2');

$builder->addDefinition('four')
	->setClass('AccessorReceiver');


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type( 'StdClassAccessor', $container->getService('one') );
Assert::same( $container->getService('one')->get(), $container->getService('service') );

Assert::type( 'AnnotatedAccessor', $container->getService('two') );
Assert::same( $container->getService('two')->get(), $container->getService('service') );

Assert::type( 'StdClassAccessor', $container->getService('three') );
Assert::same( $container->getService('three')->get(), $container->getService('service2') );

Assert::type( 'AccessorReceiver', $container->getService('four') );
