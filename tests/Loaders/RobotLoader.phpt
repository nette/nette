<?php

/**
 * Test: NetteLoader basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Loaders
 * @subpackage UnitTests
 */

/*use Nette\Loaders\RobotLoader;*/
/*use Nette\Environment;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



// temporary directory
$tempDir = dirname(__FILE__) . '/tmp';
NetteTestHelpers::purge($tempDir);
Environment::setVariable('tempDir', $tempDir);


$loader = new RobotLoader;
$loader->addDirectory('../../Nette/');
$loader->addDirectory(dirname(__FILE__));
$loader->addDirectory(dirname(__FILE__)); // purposely doubled
$loader->register();

dump( class_exists(/*Nette\*/'TestClass'), 'Class Nette\TestClass loaded?' );


__halt_compiler();

------EXPECT------
Class Nette\TestClass loaded? bool(TRUE)
