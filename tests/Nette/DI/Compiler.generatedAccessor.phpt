<?php

/**
 * Test: Nette\DI\Compiler: generated services accessors.
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Lorem
{
}

interface ILoremAccessor
{
	/** @return Lorem */
	function get();
}


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.generatedAccessor.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Lorem', $container->getService('lorem') );
Assert::notSame( $container->getService('lorem'), $container->getService('lorem2') );

Assert::type( 'ILoremAccessor', $container->getService('one') );
Assert::same( $container->getService('one')->get(), $container->getService('lorem') );

Assert::type( 'ILoremAccessor', $container->getService('two') );
Assert::same( $container->getService('two')->get(), $container->getService('lorem') );

Assert::type( 'ILoremAccessor', $container->getService('three') );
Assert::same( $container->getService('three')->get(), $container->getService('lorem2') );

Assert::type( 'ILoremAccessor', $container->getService('four') );
Assert::same( $container->getService('four')->get(), $container->getService('lorem') );
