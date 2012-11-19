<?php

/**
 * Test: Nette\DI\ContainerBuilder code generator.
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
$builder->addDefinition('one')
	->setClass('Service');
$builder->addDefinition('three')
	->setClass('Service', array('a', 'b'));

$builder->addDefinition('four')
	->setClass('Service', array('a', 'b'))
	->addSetup('methodA', array('a', 'b'))
	->addSetup('@four::methodB', array(1, 2));

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


Assert::true( $container->getService('one') instanceof Service );
Assert::false( $container->hasService('One') );
Assert::false( $container->hasService('oNe') );

Assert::same( array(), $container->getService('one')->args );
Assert::same( NULL, $container->getService('one')->methods );

Assert::true( $container->getService('three') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('three')->args );
Assert::same( NULL, $container->getService('three')->methods );

Assert::true( $container->getService('four') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('four')->args );
Assert::same( array(
	array('methodA', array('a', 'b')),
	array('methodB', array(1, 2)),
), $container->getService('four')->methods );

Assert::true( $container->getService('five') instanceof Service );
Assert::same( array(array()), $container->getService('five')->args );
Assert::same( NULL, $container->getService('five')->methods );

Assert::true( $container->getService('six') instanceof Service );
Assert::same( array(array(1 => 'a', 'b')), $container->getService('six')->args );
Assert::same( array(
	array('methodA', array('a', 'b')),
), $container->getService('six')->methods );

Assert::true( $container->getService('seven') instanceof Service );
Assert::same( array(array(1 => $container->getService('six'))), $container->getService('seven')->args );
Assert::same( array(
	array('methodA', array('a', 'b')),
	array('methodA', array()),
), $container->getService('six')->methods );

Assert::same( array(
	array('methodA', array('a')),
), $container->getService('seven')->methods );
