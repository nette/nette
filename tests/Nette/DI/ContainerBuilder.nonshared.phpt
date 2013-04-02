<?php

/**
 * Test: Nette\DI\ContainerBuilder and non-shared services.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Service
{
	function __construct()
	{
	}
}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service', array(new Nette\DI\Statement('@two', array('foo'))));

$two = $builder->addDefinition('two')
	->setParameters(array('foo', 'bar' => FALSE, 'array foobar' => NULL))
	->setClass('stdClass')
	->addSetup('$foo', $builder::literal('$foo'));

$builder->addDefinition('three')
	->setFactory($two, array('hello'));

$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';


$container = new Container;

Assert::true( $container->getService('one') instanceof Service );
Assert::false( $container->hasService('two') );
Assert::true( method_exists($container, 'createTwo') );
Assert::true( $container->getService('three') instanceof stdClass );
Assert::same( 'hello', $container->getService('three')->foo );
