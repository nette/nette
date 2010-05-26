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



require __DIR__ . '/../NetteTest/initialize.php';



$disableExit = TRUE;
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = array('first'=>array('name'=>'James Bond','email'=>'bond@007.com','street'=>'Unknown','city'=>'London','country'=>'GB',),'second'=>array('name'=>'Jim Beam','email'=>'jim@beam.com','street'=>'','city'=>'','country'=>'US',),'submit1'=>'Send',);
Nette\Debug::$productionMode = FALSE;
Nette\Debug::$consoleMode = TRUE;

require '../../examples/forms/naming-containers.php';



__halt_compiler() ?>
