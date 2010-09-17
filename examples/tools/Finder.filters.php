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
 * Restricts the search by modified time.
 * @param  string in format "[operator] date" example: >1978-01-23
 * @return Finder  provides a fluent interface
 */
Finder::extensionMethod('fancyDate', function($finder, $predicate) {
	if (!preg_match('#^(?:([!<>=]=?|<>)\s*)?(.+)$#i', $predicate, $matches)) {
		throw new \InvalidArgumentException('Date predicate format invalid.');
	}
	list(, $operator, $date) = $matches;
	$date = strtotime($date);
	return $finder->date($operator ? $operator : '=', $date);
});



/**
 * Restricts the search by size.
 * @param  string in format "[operator] size[unit]" example: >=10kB
 * @return Finder  provides a fluent interface
 */
Finder::extensionMethod('fancySize', function($finder, $predicate) {
	if (!preg_match('#^(?:([!<>=]=?|<>)\s*)?((?:\d*\.)?\d+)\s*(K|M|G|)B?$#i', $predicate, $matches)) {
		throw new \InvalidArgumentException('Size predicate format invalid.');
	}
	static $units = array('' => 1, 'k' => 1e3, 'm' => 1e6, 'g' => 1e9);
	list(, $operator, $size, $unit) = $matches;
	$size * $units[strtolower($unit)];
	return $finder->size($operator ? $operator : '=', $size * $units[strtolower($unit)]);
});



/**
 * Restricts the search by images dimensions.
 * @param  string
 * @param  string
 * @return Finder  provides a fluent interface
 */
Finder::extensionMethod('dimensions', function($finder, $width, $height){
	if (!preg_match('#^(\D+)(\d+)$#i', $width, $mW) || !preg_match('#^(\D+)?(\d+)$#i', $height, $mH)) {
		throw new \InvalidArgumentException('Dimensions predicate format invalid.');
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

	<h2>Find files modified in the last two weeks</h2>
	<?php
	foreach (Finder::findFiles('*')->fancyDate('> - 2 days')->from('..')->exclude('temp') as $file) {
		echo $file, "<br>";
	}
	?>


	<h2>Find files larger than 4 kilobytes</h2>
	<?php
	foreach (Finder::findFiles('*')->fancySize('> 4kB')->from('..')->exclude('temp') as $file) {
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