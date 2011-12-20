<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: getByClass()
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';



class Service extends Nette\Object
{
}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('Service');
$builder->addDefinition('two')
	->setClass('Nette\Object');


// compile-time
$builder->prepareClassList();

Assert::same( 'one', $builder->getByClass('service') );
Assert::same( NULL, $builder->getByClass('unknown') );
Assert::throws(function() use ($builder) {
	$builder->getByClass('Nette\Object');
}, 'Nette\DI\ServiceCreationException', 'Multiple preferred services of type Nette\Object found: one, two');


// run-time
$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->getByClass('service') instanceof Service );
Assert::same( NULL, $container->getByClass('unknown', FALSE) );

Assert::throws(function() use ($container) {
	$container->getByClass('unknown');
}, 'Nette\DI\MissingServiceException', 'Service of type unknown not found.');

Assert::throws(function() use ($container) {
	$container->getByClass('Nette\Object');
}, 'Nette\DI\MissingServiceException', 'Multiple services of type Nette\Object found.');
