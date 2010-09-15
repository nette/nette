<?php

/**
 * Nette\Image drawing example.
 * @author     David Grudl
 */


require_once __DIR__ . '/../../Nette/loader.php';

use Nette\Image,
	Nette\Debug;


Debug::enable();



$image = Image::fromBlank(300, 300);

// white background
$image->filledRectangle(0, 0, 299, 299, Image::rgb(255, 255, 255));

// black border
$image->rectangle(0, 0, 299, 299, Image::rgb(0, 0, 0));

// three ellipses
$image->filledEllipse(100, 75, 150, 150, Image::rgb(255, 255, 0, 75));
$image->filledEllipse(120, 168, 150, 150, Image::rgb(255, 0, 0, 75));
$image->filledEllipse(187, 125, 150, 150, Image::rgb(0, 0, 255, 75));

$image->send();