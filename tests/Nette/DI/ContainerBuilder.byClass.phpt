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

	static function create($arg)
	{
		TestHelpers::note(__METHOD__ . ' ' . get_class($arg));
		return new self();
	}

}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service');

$builder->addDefinition('two')
	->setFactory('@\Service::create', array('@\Service'))
	->addSetup(array('@\Service', 'create'), array('@\Service'));

$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;


Assert::true( $container->getService('one') instanceof Service );

Assert::true( $container->getService('two') instanceof Service );

Assert::same(array(
	'Service::create Service',
	'Service::create Service',
), TestHelpers::fetchNotes());
