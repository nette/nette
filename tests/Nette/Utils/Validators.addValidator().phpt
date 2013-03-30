<?php

/**
 * Test: Nette\Utils\Validators::is()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 * @subpackage UnitTests
 */

use Nette\Utils\Validators;



require __DIR__ . '/../bootstrap.php';



Validators::addValidator('class', 'class_exists');

Assert::true( Validators::is('stdClass', 'class') );
Assert::false( Validators::is('NonExistingClass', 'class') );
