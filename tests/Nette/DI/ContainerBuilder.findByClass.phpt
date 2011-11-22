<?php

/**
 * Test: Nette\DI\ContainerBuilder::findByClass() for compile-time autowiring
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

$builder->prepareClassList();

Assert::same( 'one', $builder->findByClass('service') );
Assert::same( NULL, $builder->findByClass('unknown') );
Assert::throws(function() use ($builder) {
	$builder->findByClass('Nette\Object');
}, 'Nette\DI\ServiceCreationException', 'Multiple preferred services of type Nette\Object found: one, two');
