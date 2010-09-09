<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../initialize.php';



$disableExit = TRUE;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('text'=>'a','submit1'=>'Send',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

ob_start();
require '../../examples/forms/CSRF-protection.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.002.expect'), ob_get_clean() );
