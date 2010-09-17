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
$_POST = array('name'=>'John Doe ','age'=>'  12 ','email'=>'@','street'=>'','city'=>'','country'=>'CZ','password'=>'xxx','password2'=>'xxx','note'=>'','userid'=>'231','submit1'=>'Send',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

ob_start();
require '../../examples/forms/manual-rendering.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.007.expect'), ob_get_clean() );
