<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../bootstrap.php';



$disableExit = TRUE;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('num1'=>'5','num2'=>'5','submit1'=>'Send',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

ob_start();
require '../../examples/forms/custom-validator.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.005.expect'), ob_get_clean() );
