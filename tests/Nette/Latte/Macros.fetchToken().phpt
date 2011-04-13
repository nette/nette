<?php

/**
 * Test: Nette\Latte\DefaultMacros::fetchToken()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\DefaultMacros;



require __DIR__ . '/../bootstrap.php';



$latte = new DefaultMacros;

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
