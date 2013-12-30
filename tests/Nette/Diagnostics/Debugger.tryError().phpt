<?php

/**
 * Test: Nette\Diagnostics\Debugger::tryError() & catchError.
 *
 * @author     David Grudl
 */

use Nette\Diagnostics\Debugger,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Debugger::tryError(); {
	$a++;
} $res = Debugger::catchError($e);

Assert::true( $res );
Assert::same( "Undefined variable: a", $e->getMessage() );


Debugger::tryError(); {

} $res = Debugger::catchError($e);

Assert::false( $res );
Assert::null( $e );
