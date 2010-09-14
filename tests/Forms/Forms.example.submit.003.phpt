<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

use Nette\Forms\Form;



require __DIR__ . '/../initialize.php';



$disableExit = TRUE;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('name'=>'Žlu&#357;ou&#269;ký k&#367;&#328;','country'=>array(0=>'&#268;eská republika',1=>'SlovakiaXX',2=>'Canada',),'note'=>'&#1078;&#1077;&#1076;','submit1'=>'Send','userid'=>'k&#367;&#328;',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;


ob_start();
require '../../examples/forms/custom-encoding.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.submit.003.expect'), ob_get_clean() );
