<?php

/**
 * Test: Nette\DI\ContainerBuilder::createService().
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



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

Assert::true( $one instanceof stdClass );
Assert::true( $a instanceof stdClass );
Assert::true( $b instanceof stdClass );

Assert::false( $one === $a );
Assert::false( $one === $b );
Assert::false( $a === $b );
