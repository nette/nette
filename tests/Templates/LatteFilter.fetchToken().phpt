<?php

/**
 * Test: Nette\Templates\LatteFilter::fetchToken()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\LatteFilter;



require __DIR__ . '/../initialize.php';



$s = '';
Assert::same( NULL,  LatteFilter::fetchToken($s) );
Assert::same( '',  $s );

$s = '$1d-,a';
Assert::same( '$1d-',  LatteFilter::fetchToken($s) );
Assert::same( 'a',  $s );

$s = '$1d"-,a';
Assert::same( '$1d',  LatteFilter::fetchToken($s) );
Assert::same( '"-,a',  $s );

$s = '"item\'1""item2"';
Assert::same( '"item\'1""item2"',  LatteFilter::fetchToken($s) );
Assert::same( '',  $s );
