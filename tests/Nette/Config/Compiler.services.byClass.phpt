<?php

/**
 * Test: Nette\Config\Compiler: services by Class.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config;



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




$loader = new Config\Loader;
$compiler = new Config\Compiler;
$code = $compiler->compile($loader->load('files/compiler.services.byClass.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one') instanceof Lorem );
Assert::true( $container->getService('two') instanceof Ipsum );
Assert::true( $container->getService('three') instanceof Lorem );
Assert::same( $container->getService('one'), $container->getService('three') );
Assert::true( $container->getService('four') instanceof Lorem );
Assert::same( $container->getService('one'), $container->getService('four') );
