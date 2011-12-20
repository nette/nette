<?php

/**
 * Test: Nette\Config\Configurator: nonshared services factories.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Ipsum
{
}

class Lorem
{
}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.nonshared.neon', Configurator::NONE)
	->createContainer();

Assert::false( $container->hasService('lorem') );
Assert::true( method_exists($container, 'createLorem') );

$params = new ReflectionParameter(array('SystemContainer', 'createLorem'), 0);
Assert::same( 'foo', $params->getName() );
Assert::same( 'Ipsum', $params->getClass()->getName() );
Assert::false( $params->isDefaultValueAvailable() );

$params = new ReflectionParameter(array('SystemContainer', 'createLorem'), 1);
Assert::same( 'bar', $params->getName() );
Assert::false( $params->getDefaultValue() );
