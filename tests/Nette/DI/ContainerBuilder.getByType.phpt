<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: getByType()
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Service extends Nette\Object
{
}

class Service2 extends Nette\Object
{
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service');
$builder->addDefinition('two')
	->setClass('Service2');


// compile-time
$builder->prepareClassList();

Assert::same( 'one', $builder->getByType('service') );
Assert::null( $builder->getByType('unknown') );
Assert::exception(function() use ($builder) {
	$builder->getByType('Nette\Object');
}, 'Nette\DI\ServiceCreationException', 'Multiple services of type Nette\Object found: one, two');


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type( 'Service', $container->getByType('service') );
Assert::null( $container->getByType('unknown', FALSE) );

Assert::same( array('one'), $container->findByType('service') );
Assert::same( array(), $container->findByType('unknown') );

Assert::exception(function() use ($container) {
	$container->getByType('unknown');
}, 'Nette\DI\MissingServiceException', 'Service of type unknown not found.');

Assert::exception(function() use ($container) {
	$container->getByType('Nette\Object');
}, 'Nette\DI\MissingServiceException', 'Multiple services of type Nette\Object found: one, two, container.');
