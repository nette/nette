<?php

/**
 * Test: Nette\Templates\BaseTemplate::optimizePhp()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\BaseTemplate;



require __DIR__ . '/../initialize.php';



$input = file_get_contents(__DIR__ . '/templates/optimize.phtml');
echo BaseTemplate::optimizePhp($input);



__halt_compiler() ?>
