<?php

/**
 * Test: Nette\DI\Container by tag.
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
	'debugPanel',
));
$container->addService('three', $one, NULL);
$container->addService('four', $one, array());
$container->addService('five', $one, array(
	'debugPanel' => array(1, 2, 3),
	'typeHint' => 'Service',
));


Assert::same( array(
	'five' => array('Service'),
), $container->getServiceNamesByTag('typeHint') );

Assert::same( array(
	'two' => array(),
	'five' => array(1, 2, 3),
), $container->getServiceNamesByTag('debugPanel') );

Assert::same( array(), $container->getServiceNamesByTag('unknown') );
