<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: callInjects()
 *
 * @author     Patrik Votoček
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Test
{
	public $container;
	public $obj;

	function injectPrimary(stdClass $obj, DI\Container $container)
	{
		$this->container = $container;
	}

	function injectTest(stdClass $obj)
	{
		$this->obj = $obj;
	}

}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');
$builder->addDefinition('two')
	->setClass('Test');
$builder->addDefinition('three')
	->setClass('Test')
	->setInject(FALSE);


// run-time
$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

$test = new Test;
$container->callInjects($test);
Assert::same( $container, $test->container );
Assert::equal('stdClass', get_class($test->obj) );

$two = $container->two;
Assert::same( $container, $two->container );
Assert::equal('stdClass', get_class($two->obj) );

$three = $container->three;
Assert::null( $three->container );
Assert::null( $three->obj );