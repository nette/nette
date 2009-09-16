<?php

/**
 * Test: Nette\Loaders\SimpleLoader basic usage.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Loaders
 * @subpackage UnitTests
 */

/*use Nette\Loaders\SimpleLoader;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



set_include_path('../../');

$loader = new SimpleLoader;
$loader->register();

/**/AutoLoader::load('Nette\Debug');/**/

dump( class_exists(/*Nette\*/'Debug'), 'Class Nette\Debug loaded?' );


__halt_compiler();

------EXPECT------
Class Nette\Debug loaded? bool(TRUE)
