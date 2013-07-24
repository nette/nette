<?php

/**
 * Test: Nette\Templating\Helpers::nl2br()
 *
 * @author     Filip Procházka
 * @package    Nette\Templating
 */

use Nette\Templating\Helpers;


require __DIR__ . '/../bootstrap.php';


$input = "Hello\nmy\r\nfriend\n\r";

Nette\Utils\Html::$xhtml = TRUE;
Assert::same( "Hello<br />\nmy<br />\r\nfriend<br />\n\r", Helpers::nl2br($input) );

Nette\Utils\Html::$xhtml = FALSE;
Assert::same( "Hello<br>\nmy<br>\r\nfriend<br>\n\r", Helpers::nl2br($input) );
