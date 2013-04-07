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

	static function create($arg)
	{
		Notes::add(__METHOD__ . ' ' . get_class($arg));
		return new stdClass;
	}

}

class AnnotatedFactory
{

	/** @return stdClass */
	static function create()
	{
		Notes::add(__METHOD__);
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


Assert::true( $container->getService('factory') instanceof Factory );

Assert::true( $container->getService('two') instanceof stdClass );
Assert::same(array(
	'Factory::create Factory',
	'Factory::create Factory',
), Notes::fetch());

Assert::true( $container->getService('three') instanceof stdClass );
Assert::same(array(
	'Factory::create Factory',
), Notes::fetch());

Assert::true( $container->getService('four') instanceof stdClass );
Assert::same(array(
	'AnnotatedFactory::create',
), Notes::fetch());
