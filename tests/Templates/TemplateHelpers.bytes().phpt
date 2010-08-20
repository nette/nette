<?php

/**
 * Test: Nette\Templates\TemplateHelpers::bytes()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\TemplateHelpers;



require __DIR__ . '/../initialize.php';



Assert::same( "0 B", TemplateHelpers::bytes(0.1), "TemplateHelpers::bytes(0.1)" );


Assert::same( "-1.03 GB", TemplateHelpers::bytes(-1024 * 1024 * 1050), "TemplateHelpers::bytes(-1024 * 1024 * 1050)" );


Assert::same( "8881.78 PB", TemplateHelpers::bytes(1e19), "TemplateHelpers::bytes(1e19)" );
