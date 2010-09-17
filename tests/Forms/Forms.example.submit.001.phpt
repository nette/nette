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
$_POST = array('name'=>'John Doe ','age'=>'','email'=>'  @ ','send'=>'on','street'=>'','city'=>'','country'=>'HU','password'=>'xxx','password2'=>'','note'=>'','submit1'=>'Send','userid'=>'231',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

ob_start();
require '../../examples/forms/basic-example.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.001.expect'), ob_get_clean() );
