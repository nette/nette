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



ob_start();
require '../../examples/forms/naming-containers.php';
Assert::match( file_get_contents(__DIR__ . '/Forms.example.006.expect'), ob_get_clean() );
