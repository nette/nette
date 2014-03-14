<?php

/**
 * Test: Nette\Latte\Runtime\Filters::nl2br()
 *
 * @author     Filip Procházka
 */

use Nette\Latte\Runtime\Filters,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$input = "Hello\nmy\r\nfriend\n\r";

Nette\Utils\Html::$xhtml = TRUE;
Assert::same( "Hello<br />\nmy<br />\r\nfriend<br />\n\r", Filters::nl2br($input) );

Nette\Utils\Html::$xhtml = FALSE;
Assert::same( "Hello<br>\nmy<br>\r\nfriend<br>\n\r", Filters::nl2br($input) );
