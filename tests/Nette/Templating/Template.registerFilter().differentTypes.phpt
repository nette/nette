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



$template = new Template();
$template->registerFilter(function () {});
$template->registerFilter(new Latte\Engine());
