<?php

/**
 * Test: Nette\DI\ContainerBuilder and class blacklist
 *
 * @author     David Matejka
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

interface Foo
{

}

interface Ipsum
{

}

class Bar
{

}

class Lorem extends Bar implements Foo, Ipsum
{

}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('lorem')
		->setClass('Lorem');
$builder->addClassToBlacklist('Foo');
$builder->addClassToBlacklist('Bar');
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::type('Lorem', $container->getByType('Lorem'));
Assert::type('Lorem', $container->getByType('Ipsum'));

Assert::exception(function () use ($container) {
	$container->getByType('Foo');
}, '\Nette\DI\MissingServiceException');

Assert::exception(function () use ($container) {
	$container->getByType('Bar');
}, '\Nette\DI\MissingServiceException');
