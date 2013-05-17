<?php

/**
 * Test: Nette\DI\ContainerBuilder and aliases.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';


class Service
{}

interface ServiceFactory
{
	function create();
}

interface ServiceFactory2
{
	function create();
}

$builder = new DI\ContainerBuilder;

$builder->addDefinition('aliasForFactory')
	->setFactory('@serviceFactory');

$builder->addDefinition('aliasForFactoryViaClass')
	->setFactory('@\ServiceFactory');

$builder->addDefinition('aliasedFactory')
	->setImplement('ServiceFactory')
	->setFactory('@serviceFactory');

$builder->addDefinition('aliasedFactoryViaClass')
	->setImplement('ServiceFactory')
	->setAutowired(FALSE)
	->setFactory('@\ServiceFactory');

$builder->addDefinition('aliasedService')
	->setFactory('@service');

$builder->addDefinition('aliasedServiceViaClass')
	->setFactory('@\Service');

$builder->addDefinition('serviceFactory')
	->setImplement('ServiceFactory')
	->setFactory('@service');

$builder->addDefinition('serviceFactoryViaClass')
	->setImplement('ServiceFactory2')
	->setFactory('@\Service');

$builder->addDefinition('service')
	->setClass('Service');


$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Service', $container->getService('service') );
Assert::type( 'Service', $container->getService('aliasedService') );
Assert::type( 'Service', $container->getService('aliasedServiceViaClass') );

Assert::type( 'ServiceFactory', $container->getService('serviceFactory') );
Assert::type( 'ServiceFactory2', $container->getService('serviceFactoryViaClass') );

Assert::type( 'ServiceFactory', $container->getService('aliasedFactory') );
Assert::type( 'ServiceFactory', $container->getService('aliasedFactoryViaClass') );
Assert::type( 'ServiceFactory', $container->getService('aliasForFactory') );
Assert::type( 'ServiceFactory', $container->getService('aliasForFactoryViaClass') );

// autowiring test
Assert::type( 'Service', $container->getByType('Service') );
Assert::type( 'ServiceFactory', $container->getByType('ServiceFactory') );
Assert::type( 'ServiceFactory2', $container->getByType('ServiceFactory2') );
