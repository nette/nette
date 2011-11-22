<?php

/**
 * Test: Nette\DI\ContainerBuilder code generator.
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

	static function create(DI\IContainer $container = NULL)
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
	->setClass('Service')
	->setArguments(array('a', 'b'))
	->addSetup('methodA', array('a', 'b'))
	->addSetup('@four::methodB', array(1, 2));

$builder->addDefinition('five', NULL)
	->setFactory('Service::create');

$builder->addDefinition('six')
	->setFactory('Service::create', array('@container', 'a', 'b'))
	->addSetup(array('@six', 'methodA'), array('a', 'b'));

$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one') instanceof Service );

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
Assert::equal( array(array()), $container->getService('five')->args );
Assert::same( NULL, $container->getService('five')->methods );

Assert::true( $container->getService('six') instanceof Service );
Assert::equal( array(array(1 => 'a', 'b')), $container->getService('six')->args );
Assert::same( array(
	array('methodA', array('a', 'b')),
), $container->getService('six')->methods );
