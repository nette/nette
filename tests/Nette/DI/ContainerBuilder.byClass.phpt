<?php

/**
 * Test: Nette\DI\ContainerBuilder code generator.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Factory
{
	public static $methods;

	static function create($arg)
	{
		self::$methods[] = array(__FUNCTION__, func_get_args());
		return new stdClass;
	}

}

class AnnotatedFactory
{
	public $methods;

	/** @return stdClass */
	function create()
	{
		$this->methods[] = array(__FUNCTION__, func_get_args());
		return new stdClass;
	}

}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('factory')
	->setClass('Factory');

$builder->addDefinition('annotatedFactory')
	->setClass('AnnotatedFactory');

$builder->addDefinition('two')
	->setClass('stdClass')
	->setAutowired(FALSE)
	->setFactory('@factory::create', array('@\Factory'))
	->addSetup(array('@\Factory', 'create'), array('@\Factory'));

$builder->addDefinition('three')
	->setClass('stdClass')
	->setAutowired(FALSE)
	->setFactory('@\Factory::create', array('@\Factory'));

$builder->addDefinition('four')
	->setAutowired(FALSE)
	->setFactory('@\AnnotatedFactory::create');

$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

$factory = $container->getService('factory');
Assert::true( $factory instanceof Factory );

Assert::true( $container->getService('two') instanceof stdClass );
Assert::same(array(
	array('create', array($factory)),
	array('create', array($factory)),
), Factory::$methods);

Factory::$methods = NULL;

Assert::true( $container->getService('three') instanceof stdClass );
Assert::same(array(
	array('create', array($factory)),
), Factory::$methods);

$annotatedFactory = $container->getService('annotatedFactory');
Assert::true( $annotatedFactory instanceof AnnotatedFactory );

Assert::true( $container->getService('four') instanceof stdClass );
Assert::same(array(
	array('create', array()),
), $annotatedFactory->methods);
