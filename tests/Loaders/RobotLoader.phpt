<?php

/**
 * Test: Nette\Loaders\RobotLoader basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Loaders
 * @subpackage UnitTests
 */

use Nette\Loaders\RobotLoader,
	Nette\Environment;



require __DIR__ . '/../initialize.php';



// temporary directory
define('TEMP_DIR', __DIR__ . '/tmp');
T::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);


$loader = new RobotLoader;
$loader->addDirectory('../../Nette/');
$loader->addDirectory(__DIR__);
$loader->addDirectory(__DIR__); // purposely doubled
$loader->register();

T::dump( class_exists('Nette\TestClass'), 'Class Nette\TestClass loaded?' );


__halt_compiler() ?>

------EXPECT------
Class %ns%TestClass loaded? bool(TRUE)
