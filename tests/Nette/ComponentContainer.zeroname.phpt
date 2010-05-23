<?php

/**
 * Test: Nette\ComponentContainer and '0' name.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

/*use Nette\ComponentContainer;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$container = new ComponentContainer;
$container->addComponent(new ComponentContainer, 0);
dump( $container->getComponent(0)->getName() );



__halt_compiler() ?>

------EXPECT------
string(1) "0"
