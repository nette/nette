<?php

/**
 * Test: Nette\Utils\PhpGenerator for interfaces.
 *
 * @author     David Grudl
 */

use Nette\Utils\PhpGenerator\ClassType,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$interface = new ClassType('IExample');
$interface
	->setType('interface')
	->addExtend('IOne')
	->addExtend('ITwo')
	->addDocument('Description of interface');

$interface->addMethod('getForm');

Assert::matchFile(__DIR__ . '/PhpGenerator.interface.expect', (string) $interface);
