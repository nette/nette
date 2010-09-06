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
TestHelpers::purge(TEMP_DIR);
Environment::setVariable('tempDir', TEMP_DIR);


$loader = new RobotLoader;
$loader->addDirectory('../../Nette/');
$loader->addDirectory(__DIR__);
$loader->addDirectory(__DIR__); // purposely doubled
$loader->register();

Assert::false( class_exists('ConditionalClass') );
Assert::true( class_exists('TestClass') );
Assert::true( class_exists('MySpace1\TestClass') );
Assert::true( class_exists('MySpace2\TestClass') );
