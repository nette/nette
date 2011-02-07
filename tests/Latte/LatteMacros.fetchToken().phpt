<?php

/**
 * Test: Nette\Templates\LatteMacros::fetchToken()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new LatteMacros;

$s = '';
Assert::same( NULL,  $latte->fetchToken($s) );
Assert::same( '',  $s );

$s = '$1d-,a';
Assert::same( '$1d-',  $latte->fetchToken($s) );
Assert::same( 'a',  $s );

$s = '$1d"-,a';
Assert::same( '$1d',  $latte->fetchToken($s) );
Assert::same( '"-,a',  $s );

$s = '"item\'1""item2"';
Assert::same( '"item\'1""item2"',  $latte->fetchToken($s) );
Assert::same( '',  $s );
