<?php

/**
 * Test: Nette\Templates\TemplateHelpers::date()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Templates
 * @subpackage UnitTests
 */

use Nette\Templates\TemplateHelpers;



require __DIR__ . '/../initialize.php';



T::dump( TemplateHelpers::date(NULL), "TemplateHelpers::date(NULL)" );

T::dump( TemplateHelpers::date(254400000), "TemplateHelpers::date(timestamp)" );

T::dump( TemplateHelpers::date('1978-05-05'), "TemplateHelpers::date(string)" );

T::dump( TemplateHelpers::date(new DateTime('1978-05-05')), "TemplateHelpers::date(DateTime)" );

T::dump( TemplateHelpers::date(254400000, 'Y-m-d'), "TemplateHelpers::date(timestamp, format)" );

T::dump( TemplateHelpers::date('1212-09-26', 'Y-m-d'), "TemplateHelpers::date(string, format)" );

T::dump( TemplateHelpers::date(new DateTime('1212-09-26'), 'Y-m-d'), "TemplateHelpers::date(DateTime, format)" );



__halt_compiler() ?>

------EXPECT------
TemplateHelpers::date(NULL): NULL

TemplateHelpers::date(timestamp): string(8) "01/23/78"

TemplateHelpers::date(string): string(8) "05/05/78"

TemplateHelpers::date(DateTime): string(8) "05/05/78"

TemplateHelpers::date(timestamp, format): string(10) "1978-01-23"

TemplateHelpers::date(string, format): string(10) "1212-09-26"

TemplateHelpers::date(DateTime, format): string(10) "1212-09-26"
