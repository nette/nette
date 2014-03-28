<?php

/**
 * Test: Nette\DI\ContainerFactory basic usage.
 *
 * @author     David Grudl
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$factory = new DI\ContainerFactory(TEMP_DIR);

$container = $factory->create();
Assert::type($factory->class, $container);
Assert::type($factory->parentClass, $container);

$container = $factory->create();
Assert::type($factory->class, $container);
Assert::type($factory->parentClass, $container);

$factory->class = 'My';
$container = $factory->create();
Assert::type($factory->class, $container);
Assert::type($factory->parentClass, $container);
