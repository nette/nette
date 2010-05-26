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



require __DIR__ . '/../NetteTest/initialize.php';



$loader = NetteLoader::getInstance();
$loader->base = '../../Nette';
$loader->register();

dump( class_exists('Nette\Debug'), 'Class Nette\Debug loaded?' );



__halt_compiler() ?>

------EXPECT------
Class Nette\Debug loaded? bool(TRUE)
