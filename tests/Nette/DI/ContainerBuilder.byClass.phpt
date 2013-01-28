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

	static function create($arg)
	{
	Notes::add(__METHOD__ . ' ' . get_class($arg));
	return new self();
	}

}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service');

$builder->addDefinition('two')
	->setClass('Service')
	->setAutowired(FALSE)
	->setFactory('@one::create', array('@\Service'))
	->addSetup(array('@\Service', 'create'), array('@\Service'));

$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one') instanceof Service );

Assert::true( $container->getService('two') instanceof Service );

Assert::same(array(
	'Service::create Service',
	'Service::create Service',
), Notes::fetch());
