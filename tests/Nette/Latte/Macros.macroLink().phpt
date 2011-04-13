<?php

/**
 * Test: Nette\Latte\DefaultMacros::macroLink()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';


$macros = new DefaultMacros;

// {link ...}
Assert::same( '$control->link("p")',  $macros->macroLink('p', '') );
Assert::same( '$template->filter($control->link("p"))',  $macros->macroLink('p', 'filter') );
Assert::same( '$control->link("p:a")',  $macros->macroLink('p:a', '') );
Assert::same( '$control->link($dest)',  $macros->macroLink('$dest', '') );
Assert::same( '$control->link($p:$a)',  $macros->macroLink('$p:$a', '') );
Assert::same( '$control->link("$p:$a")',  $macros->macroLink('"$p:$a"', '') );
Assert::same( '$control->link("p:a")',  $macros->macroLink('"p:a"', '') );
Assert::same( '$control->link(\'p:a\')',  $macros->macroLink("'p:a'", '') );

Assert::same( '$control->link("p", array(\'param\'))',  $macros->macroLink('p param', '') );
Assert::same( '$control->link("p", array(\'param\' => 123))',  $macros->macroLink('p param => 123', '') );
Assert::same( '$control->link("p", array(\'param\' => 123))',  $macros->macroLink('p, param => 123', '') );
