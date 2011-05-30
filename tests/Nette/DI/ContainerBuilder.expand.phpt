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
$container->params['serviceClass'] = 'Service';
$container->params['arg1'] = 'a';
$container->params['tag'] = 'attrs';

$builder = new DI\ContainerBuilder;

$builder->addDefinitions($container, array(
	'one' => array(
		'class' => '%serviceClass%',
		'arguments' => array(
			'%arg1%', 'b',
		),
		'methods' => array(
			array('methodA', array('%arg1%', 'b')),
		),
		'tags' => array(
			'panel' => '%tag%',
		)
	),
	'two' => array(
		'factory' => '%serviceClass%::create',
		'arguments' => array(
			'%arg1%', '@one',
		),
	),
	'three' => array(
		'factory' => array('%serviceClass%', 'create'),
	),
));

Assert::true( $container->getService('one') instanceof Service );
Assert::same( array('a', 'b'), $container->getService('one')->args );
Assert::same( array(array('methodA', array('a', 'b'))), $container->getService('one')->methods );
Assert::same( array('one' => array('attrs')), $container->getServiceNamesByTag('panel') );

Assert::true( $container->getService('two') instanceof Service );
Assert::equal( array(array(1 => 'a', $container->getService('one'))), $container->getService('two')->args );

Assert::true( $container->getService('three') instanceof Service );


$builder->addDefinitions($container, array(
	'bad' => array(
		'class' => '%missing%',
	)
));
try {
	$container->getService('bad');
	Assert::fail('Expected exception');
} catch (Exception $e) {
	Assert::exception('Nette\InvalidArgumentException', "Missing item 'missing'.", $e );
}
