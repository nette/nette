<?php

/**
 * Test: Nette\Templates\BaseTemplate::optimizePhp()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\BaseTemplate;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$input = file_get_contents(dirname(__FILE__) . '/templates/optimize.phtml');
echo BaseTemplate::optimizePhp($input);



__halt_compiler() ?>
