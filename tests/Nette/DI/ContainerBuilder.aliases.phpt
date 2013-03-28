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


Assert::true( $container->getService('service') instanceof Service );
Assert::true( $container->getService('aliasedService') instanceof Service );
Assert::true( $container->getService('aliasedServiceViaClass') instanceof Service );

Assert::true( $container->getService('serviceFactory') instanceof ServiceFactory );
Assert::true( $container->getService('serviceFactoryViaClass') instanceof ServiceFactory2 );

Assert::true( $container->getService('aliasedFactory') instanceof ServiceFactory );
Assert::true( $container->getService('aliasedFactoryViaClass') instanceof ServiceFactory );
Assert::true( $container->getService('aliasForFactory') instanceof ServiceFactory );
Assert::true( $container->getService('aliasForFactoryViaClass') instanceof ServiceFactory );

// autowiring test
Assert::true( $container->getByType('Service') instanceof Service );
Assert::true( $container->getByType('ServiceFactory') instanceof ServiceFactory );
Assert::true( $container->getByType('ServiceFactory2') instanceof ServiceFactory2 );
