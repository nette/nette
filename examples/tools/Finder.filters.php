<?php

/**
 * Nette\Finder custom filters.
 * @author     David Grudl
 * @phpversion 5.3
 */


require_once __DIR__ . '/../../Nette/loader.php';

use Nette\Finder,
	Nette\Tools,
	Nette\Debug;


Debug::enable();



/**
 * Restricts the search by number of lines.
 * @param  string
 * @return Finder  provides a fluent interface
 */
Finder::extensionMethod('lines', function($finder, $predicate){
	if (!preg_match('#^([=<>!]+)\s*(\d+)$#i', $predicate, $matches)) {
		throw new \InvalidArgumentException('Invalid lines predicate format.');
	}
	list(, $operator, $nubmer) = $matches;
	return $finder->filter(function($file) use ($operator, $nubmer) {
		return Tools::compare(count(file($file->getPathname())), $operator, $nubmer);
	});
});



/**
 * Restricts the search by images dimensions.
 * @param  string
 * @param  string
 * @return Finder  provides a fluent interface
 */
Finder::extensionMethod('dimensions', function($finder, $width, $height){
	if (!preg_match('#^([=<>!]+)\s*(\d+)$#i', $width, $mW) || !preg_match('#^([=<>!]+)\s*(\d+)$#i', $height, $mH)) {
		throw new \InvalidArgumentException('Invalid dimensions predicate format.');
	}
	return $finder->filter(function($file) use ($mW, $mH) {
		return $file->getSize() >= 12 && ($size = getimagesize($file->getPathname()))
			&& (!$mW || Tools::compare($size[0], $mW[1], $mW[2]))
			&& (!$mH || Tools::compare($size[1], $mH[1], $mH[2]));
	});
});





?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<title>Nette\Finder custom filters | Nette Framework</title>

	<link rel="stylesheet" type="text/css" media="screen" href="files/style.css" />
</head>

<body>
	<h1>Nette\Finder custom filters</h1>

	<h2>Find PHP files longer than 100 lines</h2>
	<?php
	foreach (Finder::findFiles('*.php')->lines('> 100')->from('..')->exclude('temp') as $file) {
		echo $file, "<br>";
	}
	?>


	<h2>Find images with dimensions greater than 50px x 50px</h2>
	<?php
	foreach (Finder::findFiles('*')->dimensions('>50', '>50')->from('..')->exclude('temp') as $file) {
		echo $file, "<br>";
	}
	?>
</body>
</html>