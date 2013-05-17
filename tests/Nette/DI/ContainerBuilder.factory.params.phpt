<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories with parameters.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



interface StdClassFactory
{
	function create(stdClass $a, array $b, $c = NULL);
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setImplement('stdClassFactory')
	->setFactory('stdClass')
	->addSetup('$a', $builder::literal('$a'));

$builder->addDefinition('two')
	->setParameters(array('stdClass foo', 'array bar', 'foobar' => NULL))
	->setImplement('stdClassFactory')
	->setFactory('stdClass')
	->addSetup('$a', $builder::literal('$foo'));

$builder->addDefinition('three')
	->setClass('stdClass');

$builder->addDefinition('four')
	->setFactory('@one::create', array(1 => array(1)))
	->setAutowired(FALSE);

$builder->addDefinition('five')
	->setFactory('@two::create', array(1 => array(1)))
	->setAutowired(FALSE);

// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type( 'StdClassFactory', $container->getService('one') );
Assert::type( 'StdClassFactory', $container->getService('two') );

Assert::type( 'stdClass', $container->getService('four') );
Assert::same( $container->getService('four')->a, $container->getService('three') );

Assert::type( 'stdClass', $container->getService('five') );
Assert::same( $container->getService('five')->a, $container->getService('three') );
