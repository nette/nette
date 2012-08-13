<?php

/**
 * Test: Nette\Templating\Template::registerFilter()
 *
 * @author     Josef Cech
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Templating\Template;
use Nette\Latte;



require __DIR__ . '/../bootstrap.php';



Assert::throws(function() {
	$filter = new Latte\Engine();
	$template = new Template();
	$template->registerFilter($filter);
	$template->registerFilter($filter);
}, 'Nette\InvalidStateException', "Filter 'Nette\Latte\Engine::__invoke' was registered twice.");

Assert::throws(function() {
	$template = new Template();
	$template->registerFilter('strtolower');
	$template->registerFilter('strtolower');
}, 'Nette\InvalidStateException', "Filter 'strtolower' was registered twice.");
