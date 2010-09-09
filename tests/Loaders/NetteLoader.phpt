<?php

/**
 * Test: Nette\Loaders\NetteLoader basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Loaders
 * @subpackage UnitTests
 */

use Nette\Loaders\NetteLoader;



require __DIR__ . '/../initialize.php';



$loader = NetteLoader::getInstance();
$loader->register();

Assert::true( class_exists('Nette\Debug'), 'Class Nette\Debug loaded?' );
