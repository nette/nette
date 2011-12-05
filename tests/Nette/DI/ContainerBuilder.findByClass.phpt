<?php

/**
 * Test: Nette\DI\ContainerBuilder and Container: findByClass()
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

Assert::same( 'one', $builder->findByClass('service') );
Assert::same( NULL, $builder->findByClass('unknown') );
Assert::throws(function() use ($builder) {
	$builder->findByClass('Nette\Object');
}, 'Nette\DI\ServiceCreationException', 'Multiple preferred services of type Nette\Object found: one, two, container');


// run-time
$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->findByClass('service') instanceof Service );
Assert::same( NULL, $container->findByClass('unknown') );
Assert::throws(function() use ($container) {
	$container->findByClass('Nette\Object');
}, 'Nette\DI\ServiceCreationException', 'Multiple services of type Nette\Object found.');
