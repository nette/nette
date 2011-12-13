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
	public $container;

	function __construct(stdClass $obj, DI\Container $container)
	{
		$this->container = $container;
	}

	function method(stdClass $obj, DI\Container $container)
	{
		return isset($obj->prop);
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

$test = $container->createInstance('Test');
Assert::true( $test instanceof Test );
Assert::same( $container, $test->container );
Assert::same( FALSE, $container->callMethod(array($test, 'method')) );
Assert::same( TRUE, $container->callMethod(array($test, 'method'), array((object) array('prop' => TRUE))) );
