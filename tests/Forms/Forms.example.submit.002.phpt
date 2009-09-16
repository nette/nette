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
$_POST = array('text'=>'a','submit1'=>'Send',);
/*Nette\*/Debug::$productionMode = FALSE;
/*Nette\*/Debug::$consoleMode = TRUE;

require '../../examples/forms/CSRF-protection.php';



__halt_compiler();
