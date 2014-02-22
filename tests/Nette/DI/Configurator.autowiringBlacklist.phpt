<?php

/**
 * Test: Nette\Configurator and autowiring blacklist
 *
 * @author     David Matejka
 */

use Nette\Configurator,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FooPresenter extends \Nette\Application\UI\Presenter
{

}


$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);

$container = $configurator->addConfig('files/configurator.autowiringBlacklist.neon')
						  ->createContainer();


Assert::type('FooPresenter', $container->getByType('FooPresenter'));
Assert::exception(function () use ($container) {
	$container->getByType('Nette\Application\UI\Presenter');
}, '\Nette\DI\MissingServiceException');
