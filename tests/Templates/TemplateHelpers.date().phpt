<?php

/**
 * Test: Nette\Templates\TemplateHelpers::date()
 *
 * @author     David Grudl
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\TemplateHelpers;



require __DIR__ . '/../initialize.php';



Assert::null( TemplateHelpers::date(NULL), "TemplateHelpers::date(NULL)" );


Assert::same( "01/23/78", TemplateHelpers::date(254400000), "TemplateHelpers::date(timestamp)" );


Assert::same( "05/05/78", TemplateHelpers::date('1978-05-05'), "TemplateHelpers::date(string)" );


Assert::same( "05/05/78", TemplateHelpers::date(new DateTime('1978-05-05')), "TemplateHelpers::date(DateTime)" );


Assert::same( "1978-01-23", TemplateHelpers::date(254400000, 'Y-m-d'), "TemplateHelpers::date(timestamp, format)" );


Assert::same( "1212-09-26", TemplateHelpers::date('1212-09-26', 'Y-m-d'), "TemplateHelpers::date(string, format)" );


Assert::same( "1212-09-26", TemplateHelpers::date(new DateTime('1212-09-26'), 'Y-m-d'), "TemplateHelpers::date(DateTime, format)" );
