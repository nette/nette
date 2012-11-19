<?php

/**
 * Test: Nette\DI\ContainerBuilder.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Service
{
	public $args;
	public $methods;

	static function create(DI\Container $container = NULL)
	{
		$args = func_get_args();
		unset($args[0]);
		return new self($args);
	}

	function __construct()
	{
		$this->args = func_get_args();
	}

	function __call($nm, $args)
	{
		$this->methods[] = array($nm, $args);
	}

}



$builder = new DI\ContainerBuilder;
$builder->parameters = array(
	'serviceClass' => 'Service',
	'arg1' => 'a',
	'tag' => 'attrs',
);
$builder->addDefinition('one')
	->setClass('%serviceClass%', array('%arg1%', 'b'))
	->addSetup('methodA', array('%arg1%', 'b'));

$builder->addDefinition('two')
	->setFactory('%serviceClass%::create', array('@container', '%arg1%', '@one'));

$builder->addDefinition('three')
	->setFactory(array('%serviceClass%', 'create'));


$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('one')->args );
Assert::same( array(array('methodA', array('a', 'b'))), $container->getService('one')->methods );

Assert::true( $container->getService('two') instanceof Service );
Assert::same( array(array(1 => 'a', $container->getService('one'))), $container->getService('two')->args );

Assert::true( $container->getService('three') instanceof Service );
