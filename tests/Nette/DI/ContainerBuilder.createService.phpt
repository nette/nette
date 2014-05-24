<?php

/**
 * Test: Nette\DI\ContainerBuilder::createService().
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

$one = $container->getService('one');
$a = $container->createService('one');
$b = $container->createService('one');

Assert::type( 'stdClass', $one );
Assert::type( 'stdClass', $a );
Assert::type( 'stdClass', $b );

Assert::notSame( $one, $a );
Assert::notSame( $one, $b );
Assert::notSame( $a, $b );
