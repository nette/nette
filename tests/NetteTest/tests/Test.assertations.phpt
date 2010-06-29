<?php

/**
 * Test: assertations.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Test
 * @subpackage UnitTests
 */

require __DIR__ . '/initialize.php';



TestHelpers::note('OK');

Assert::true( TRUE );

Assert::false( FALSE );

Assert::null( NULL );

Assert::same( 1, 1 );


TestHelpers::note('FAILURE');

Assert::true( FALSE );

Assert::false( TRUE );

Assert::null( 'null' );

Assert::same( 1, 1.0 );

Assert::same( 1, array(1, 2, 3) );

Assert::same( 1, (object) array(1, 2, 3) );

Assert::same( 1, fopen(__FILE__, 'r') );



__halt_compiler() ?>

------EXPECT------
OK

FAILURE

Failed asserting that FALSE is not TRUE in file %a%Test.assertations.phpt on line 29

Failed asserting that TRUE is not FALSE in file %a%Test.assertations.phpt on line 31

Failed asserting that 'null' is not NULL in file %a%Test.assertations.phpt on line 33

Failed asserting that 1 is not identical to 1 in file %a%Test.assertations.phpt on line 35

Failed asserting that array(3) is not identical to 1 in file %a%Test.assertations.phpt on line 37

Failed asserting that object(stdClass) (3) is not identical to 1 in file %a%Test.assertations.phpt on line 39

Failed asserting that resource(stream) is not identical to 1 in file %a%Test.assertations.phpt on line 41
