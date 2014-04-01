<?php

/**
 * Test: Latte\Engine: errors.
 *
 * @author     David Grudl
 */

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);

Assert::exception(function() use ($latte) {
	$latte->compile('<a {if}n:href>');
}, 'Latte\CompileException', 'Macro-attributes must not appear inside macro; found n:href inside {if}.');


Assert::exception(function() use ($latte) {
	$latte->compile('<a n:href n:href>');
}, 'Latte\CompileException', 'Found multiple macro-attributes n:href.');
