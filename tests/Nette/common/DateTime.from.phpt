<?php

/**
 * Test: Nette\DateTime test.
 *
 * @author     David Grudl
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


date_default_timezone_set('Europe/Prague');

Assert::same( '1978-01-23 11:40:00', (string) Nette\DateTime::from(254400000) );
Assert::same( 254400000, Nette\DateTime::from(254400000)->getTimestamp() );

Assert::same( '2050-08-13 11:40:00', (string) Nette\DateTime::from(2544000000) );
Assert::same( is_int(2544000000) ? 2544000000 : '2544000000', Nette\DateTime::from(2544000000)->getTimestamp() ); // 64 bit

Assert::same( '1978-05-05 00:00:00', (string) Nette\DateTime::from('1978-05-05') );

Assert::type( 'Nette\DateTime', Nette\DateTime::from(new DateTime('1978-05-05')) );

Assert::same( '1978-05-05 00:00:00', (string) Nette\DateTime::from(new DateTime('1978-05-05')) );
