<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: createInstance()
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Test
{
	function __construct(stdClass $obj, DI\Container $container)
	{
	}
}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');


// run-time
$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->createInstance('Test') instanceof Test );
