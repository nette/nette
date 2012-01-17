<?php

/**
 * Test: Nette\Latte\Engine: iCal template
 *
 * @author     David Grudl
 * @package    Nette\Latte
 * @subpackage UnitTests
 * @keepTrailingSpaces
 */

use Nette\Latte,
	Nette\Templating\FileTemplate;



require __DIR__ . '/../bootstrap.php';

require __DIR__ . '/Template.inc';



$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$template = new FileTemplate(__DIR__ . '/templates/ical.latte');
$template->registerHelper('escape', 'Nette\Templating\Helpers::escapeICal');
$template->registerFilter(new Latte\Engine);
$template->registerHelperLoader('Nette\Templating\Helpers::loader');
$template->netteHttpResponse = $container->httpResponse;

$path = __DIR__ . '/expected/' . basename(__FILE__, '.phpt');
Assert::match(file_get_contents("$path.phtml"), codefix($template->compile()));
Assert::match(file_get_contents("$path.html"), $template->__toString(TRUE));
