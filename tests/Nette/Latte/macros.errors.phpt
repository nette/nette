<?php

/**
 * Test: Nette\Latte\Engine: errors.
 *
 * @author     David Grudl
 * @package    Nette\Latte
 */

use Nette\Latte;


require __DIR__ . '/../bootstrap.php';


$template = new Nette\Templating\Template;
$template->registerFilter(new Latte\Engine);

Assert::exception(function() use ($template) {
	$template->setSource('<a {if}n:href>')->compile();
}, 'Nette\Latte\CompileException', 'Macro-attributes must not appear inside macro; found n:href inside {if}.');


Assert::exception(function() use ($template) {
	$template->setSource('<a n:href n:href>')->compile();
}, 'Nette\Latte\CompileException', 'Found multiple macro-attributes n:href.');
