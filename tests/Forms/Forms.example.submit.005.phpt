<?php

/**
 * Test: Nette\Forms example.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Forms
 * @subpackage UnitTests
 */

/*use Nette\Forms\Form;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$disableExit = TRUE;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('num1'=>'5','num2'=>'5','submit1'=>'Send',);
/*Nette\*/Debug::$productionMode = FALSE;
/*Nette\*/Debug::$consoleMode = TRUE;

require '../../examples/forms/custom-validator.php';



__halt_compiler() ?>
