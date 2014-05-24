<?php

/**
 * Test: Nette\DI\ContainerBuilder code generator.
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Service
{
	public $methods;

	static function create(DI\Container $container = NULL)
	{
		return new self(array_slice(func_get_args(), 1));
	}

	function __construct()
	{
		$this->methods[] = array(__FUNCTION__, func_get_args());
	}

	function __call($nm, $args)
	{
		$this->methods[] = array($nm, $args);
	}

}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service');
$builder->addDefinition('three')
	->setClass('Service', array('a', 'b'));

$builder->addDefinition('four')
	->setClass('Service', array('a', 'b'))
	->addSetup('methodA', array('a', 'b'))
	->addSetup('@four::methodB', array(1, 2))
	->addSetup('methodC', array('@self', '@container'))
	->addSetup('methodD', array('@one'));

$builder->addDefinition('five', NULL)
	->setFactory('Service::create');

$six = $builder->addDefinition('six')
	->setFactory('Service::create', array('@container', 'a', 'b'))
	->addSetup(array('@six', 'methodA'), array('a', 'b'));

$builder->addDefinition('seven')
	->setFactory(array($six, 'create'), array($builder, $six))
	->addSetup(array($six, 'methodA'))
	->addSetup('$service->methodA(?)', array('a'));

$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::type( 'Service', $container->getService('one') );
Assert::false( $container->hasService('One') );
Assert::false( $container->hasService('oNe') );

Assert::same( array(
	array('__construct', array())
), $container->getService('one')->methods );

Assert::type( 'Service', $container->getService('three') );
Assert::same( array(
	array('__construct', array('a', 'b'))
), $container->getService('three')->methods );

Assert::type( 'Service', $container->getService('four') );
Assert::same( array(
	array('__construct', array('a', 'b')),
	array('methodA', array('a', 'b')),
	array('methodB', array(1, 2)),
	array('methodC', array($container->getService('four'), $container)),
	array('methodD', array($container->getService('one'))),
), $container->getService('four')->methods );

Assert::type( 'Service', $container->getService('five') );
Assert::same( array(
	array('__construct', array(array()))
), $container->getService('five')->methods );

Assert::type( 'Service', $container->getService('six') );
Assert::same( array(
	array('__construct', array(array('a', 'b'))),
	array('methodA', array('a', 'b')),
), $container->getService('six')->methods );

Assert::type( 'Service', $container->getService('seven') );
Assert::same( array(
	array('__construct', array(array('a', 'b'))),
	array('methodA', array('a', 'b')),
	array('methodA', array()),
), $container->getService('six')->methods );

Assert::same( array(
	array('__construct', array(array($container->getService('six')))),
	array('methodA', array('a')),
), $container->getService('seven')->methods );
