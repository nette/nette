<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: createInstance()
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Test1
{
	public $injects;

	function inject(stdClass $obj)
	{
		$this->injects[] = __METHOD__;
	}

	function injectA(stdClass $obj)
	{
		$this->injects[] = __METHOD__;
	}

	protected function injectB(stdClass $obj)
	{
		$this->injects[] = __METHOD__;
	}

}

class Test2 extends Test1
{

	function injectC(stdClass $obj)
	{
		$this->injects[] = __METHOD__;
	}

}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('stdClass');


// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

$test = new Test2;
$container->callInjects($test);
Assert::same( array('Test1::injectA', 'Test1::inject', 'Test2::injectC'), $test->injects );
