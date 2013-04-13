<?php

/**
 * Test: Nette\DI\Compiler: generated services accessors.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



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


Assert::true( $container->getService('lorem') instanceof Lorem );
Assert::true( $container->getService('lorem') !== $container->getService('lorem2') );

Assert::true( $container->getService('one') instanceof ILoremAccessor );
Assert::true( $container->getService('one')->get() === $container->getService('lorem') );

Assert::true( $container->getService('two') instanceof ILoremAccessor );
Assert::true( $container->getService('two')->get() === $container->getService('lorem') );

Assert::true( $container->getService('three') instanceof ILoremAccessor );
Assert::true( $container->getService('three')->get() === $container->getService('lorem2') );

Assert::true( $container->getService('four') instanceof ILoremAccessor );
Assert::true( $container->getService('four')->get() === $container->getService('lorem') );
