<?php

/**
 * Test: Nette\ComponentContainer and '0' name.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\ComponentContainer;



require __DIR__ . '/../initialize.php';



$container = new ComponentContainer;
$container->addComponent(new ComponentContainer, 0);
T::dump( $container->getComponent(0)->getName() );



__halt_compiler() ?>

------EXPECT------
"0"
