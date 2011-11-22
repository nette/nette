<?php

/**
 * Test: Nette\DI\Container::findByTag()
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI\Container;



require __DIR__ . '/../bootstrap.php';



class Service implements Countable
{
	function count(){}
}

$one = new Service;
$two = new Service;


$container = new Container;
$container->addService('two', $one, array(
	Container::TAGS => array('debugPanel' => TRUE),
));
$container->addService('three', $one);
$container->addService('four', $one, array());
$container->addService('five', $one, array(
	Container::TAGS => array(
		'debugPanel' => array(1, 2, 3),
		'typeHint' => 'Service',
	)
));


Assert::same( array(
	'five' => 'Service',
), $container->findByTag('typeHint') );

Assert::same( array(
	'two' => TRUE,
	'five' => array(1, 2, 3),
), $container->findByTag('debugPanel') );

Assert::same( array(), $container->findByTag('unknown') );
