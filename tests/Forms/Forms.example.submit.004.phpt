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
$_POST = array('name'=>'John Doe ','age'=>'9.9','email'=>'@','street'=>'','city'=>'Troubsko','country'=>'0','password'=>'xx','password2'=>'xx','note'=>'','submit1'=>'Send','userid'=>'231',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

ob_start();
require '../../examples/forms/custom-rendering.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.004.expect'), ob_get_clean() );
