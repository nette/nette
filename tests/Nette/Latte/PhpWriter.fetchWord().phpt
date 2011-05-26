<?php

/**
 * Test: Nette\Latte\PhpWriter::fetchWord()
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 */

use Nette\Latte\PhpWriter;



require __DIR__ . '/../bootstrap.php';



$latte = new PhpWriter;

$s = '';
Assert::same( FALSE,  $latte->fetchWord($s) );
Assert::same( '',  $s );

$s = '$1d-,a';
Assert::same( '$1d-',  $latte->fetchWord($s) );
Assert::same( 'a',  $s );

$s = '$1d"-,a';
Assert::same( '$1d',  $latte->fetchWord($s) );
Assert::same( '"-,a',  $s );

$s = '"item\'1""item2"';
Assert::same( '"item\'1""item2"',  $latte->fetchWord($s) );
Assert::same( '',  $s );
