<?php

/**
 * Test: Nette\Templating\DefaultHelpers::bytes()
 *
 * @author     David Grudl
 * @package    Nette\Templating
 * @subpackage UnitTests
 */

use Nette\Templating\DefaultHelpers;



require __DIR__ . '/../bootstrap.php';



Assert::same( "0 B", DefaultHelpers::bytes(0.1), "TemplateHelpers::bytes(0.1)" );


Assert::same( "-1.03 GB", DefaultHelpers::bytes(-1024 * 1024 * 1050), "TemplateHelpers::bytes(-1024 * 1024 * 1050)" );


Assert::same( "8881.78 PB", DefaultHelpers::bytes(1e19), "TemplateHelpers::bytes(1e19)" );
