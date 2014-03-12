<?php

/**
 * Test: Nette\Image cloning.
 *
 * @author     MzK Olda Salek
 */

use Nette\Image,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';

if (!extension_loaded('gd')) {
	Tester\Environment::skip('Requires PHP extension GD.');
}

$original = Image::fromFile(__DIR__.'/images/logo.gif');


test(function() use ($original) { // grayscale
	$clone = clone $original;
	$clone->filter(IMG_FILTER_GRAYSCALE);
	Assert::notSame($original->toString(Image::GIF), $clone->toString(Image::GIF));
});

