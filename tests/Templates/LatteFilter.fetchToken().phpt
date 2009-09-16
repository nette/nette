<?php

/**
 * Test: Nette\Templates\LatteFilter::fetchToken()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

/*use Nette\Templates\LatteFilter;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$s = '';
dump( LatteFilter::fetchToken($s) ); // ''
dump( $s ); // ''

$s = '$1d-,a';
dump( LatteFilter::fetchToken($s) ); // '$1d-'
dump( $s ); // 'a'

$s = '$1d"-,a';
dump( LatteFilter::fetchToken($s) ); // '$1d'
dump( $s ); // '"-,a'

$s = '"item\'1""item2"';
dump( LatteFilter::fetchToken($s) ); // '"item\'1""item2"'
dump( $s ); // ''



__halt_compiler();

------EXPECT------
NULL

string(0) ""

string(4) "$1d-"

string(1) "a"

string(3) "$1d"

string(4) ""-,a"

string(15) ""item'1""item2""

string(0) ""
