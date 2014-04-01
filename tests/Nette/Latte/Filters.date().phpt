<?php

/**
 * Test: Latte\Runtime\Filters::date()
 *
 * @author     David Grudl
 */

use Latte\Runtime\Filters,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setlocale(LC_TIME, 'C');


Assert::null( Filters::date(NULL) );


Assert::same( "01/23/78", Filters::date(254400000) );


Assert::same( "05/05/78", Filters::date('1978-05-05') );


Assert::same( "05/05/78", Filters::date(new DateTime('1978-05-05')) );


Assert::same( "1978-01-23", Filters::date(254400000, 'Y-m-d') );


Assert::same( "1212-09-26", Filters::date('1212-09-26', 'Y-m-d') );


Assert::same( "1212-09-26", Filters::date(new DateTime('1212-09-26'), 'Y-m-d') );


Assert::same( "30:10:10", Filters::date(new DateInterval('PT30H10M10S'), '%H:%I:%S') );
