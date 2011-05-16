<?php

/**
 * Test: Nette\DI\ContainerBuilder.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Service
{
	public $args;
	public $methods;

	static function create(DI\IContainer $container)
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



$container = new DI\Container;
$builder = new DI\ContainerBuilder;

$builder->addDefinitions($container, array(
	'one' => 'Service',
	'two' => array(
		'class' => 'Service',
	),
	'three' => array(
		'class' => 'Service',
		'arguments' => array(
			'a', 'b',
		),
		'tags' => array(
			'panel' => 'attrs',
		)
	),
	'four' => array(
		'class' => 'Service',
		'arguments' => array(
			'a', 'b',
		),
		'methods' => array(
			array('methodA', array('a', 'b')),
			array('methodB', array(1, 2)),
		),
	),
	'five' => array(
		'factory' => 'Service::create',
	),
	'six' => array(
		'factory' => 'Service::create',
		'arguments' => array(
			'a', 'b',
		),
	),
));

Assert::true( $container->getService('one') instanceof Service );

Assert::true( $container->getService('two') instanceof Service );
Assert::same( array(), $container->getService('two')->args );
Assert::same( NULL, $container->getService('two')->methods );

Assert::true( $container->getService('three') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('three')->args );
Assert::same( NULL, $container->getService('three')->methods );
Assert::same( array('three' => array('attrs')), $container->getServiceNamesByTag('panel') );

Assert::true( $container->getService('four') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('four')->args );
Assert::same( array(
	array('methodA', array('a', 'b')),
	array('methodB', array(1, 2)),
), $container->getService('four')->methods );

Assert::true( $container->getService('five') instanceof Service );
Assert::equal( array(array()), $container->getService('five')->args );
Assert::same( NULL, $container->getService('five')->methods );

Assert::true( $container->getService('six') instanceof Service );
Assert::equal( array(array(1 => 'a', 'b')), $container->getService('six')->args );
Assert::same( NULL, $container->getService('six')->methods );
