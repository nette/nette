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



require __DIR__ . '/../NetteTest/initialize.php';



dump( TemplateHelpers::bytes(0.1), "TemplateHelpers::bytes(0.1)" );

dump( TemplateHelpers::bytes(-1024 * 1024 * 1050), "TemplateHelpers::bytes(-1024 * 1024 * 1050)" );

dump( TemplateHelpers::bytes(1e19), "TemplateHelpers::bytes(1e19)" );



__halt_compiler() ?>

------EXPECT------
TemplateHelpers::bytes(0.1): string(3) "0 B"

TemplateHelpers::bytes(-1024 * 1024 * 1050): string(8) "-1.03 GB"

TemplateHelpers::bytes(1e19): string(10) "8881.78 PB"
