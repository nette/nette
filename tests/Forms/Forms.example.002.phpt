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



ob_start();
require '../../examples/forms/CSRF-protection.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.002.expect'), ob_get_clean() );
