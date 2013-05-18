<?php

/**
 * Test: Nette\DI\Compiler: services by Class.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Lorem
{
	function __construct(Ipsum $arg)
	{
	}
}

class Ipsum
{
	static function foo()
	{
	}
}




$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.byClass.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Lorem', $container->getService('one') );
Assert::type( 'Ipsum', $container->getService('two') );
Assert::type( 'Lorem', $container->getService('three') );
Assert::same( $container->getService('one'), $container->getService('three') );
Assert::type( 'Lorem', $container->getService('four') );
Assert::same( $container->getService('one'), $container->getService('four') );
