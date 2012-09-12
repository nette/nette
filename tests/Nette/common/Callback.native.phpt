<?php

/**
 * Test: Nette\Callback tests.
 *
 * @author     David Grudl
 * @package    Nette
 */

use Nette\Callback;



require __DIR__ . '/../bootstrap.php';



class Test
{
	static function add($a, $b)
	{
		return $a + $b;
	}

	function __invoke()
	{
	}

}


Assert::same( 'undefined', Callback::create('undefined')->getNative() );
Assert::same( 'undefined', (string) Callback::create('undefined') );

Assert::same( 'trim', Callback::create('trim')->getNative() );
Assert::same( 'trim', (string) Callback::create('trim') );
Assert::same( 'Function trim()', (string) Callback::create('trim')->toReflection() );

Assert::same( array('Test', 'add'), Callback::create('Test', 'add')->getNative() );
Assert::same( 'Test::add', (string) Callback::create('Test', 'add') );
Assert::same( 'Method Test::add()', (string) Callback::create('Test', 'add')->toReflection() );

Assert::same( 'Test::add', Callback::create('Test::add')->getNative() );
Assert::same( 'Test::add', (string) Callback::create('Test::add') );
Assert::same( 'Method Test::add()', (string) Callback::create('Test::add')->toReflection() );

$test = new Test;
Assert::same( array($test, 'add'), Callback::create($test, 'add')->getNative() );
Assert::same( 'Test::add', (string) Callback::create($test, 'add') );
Assert::same( 'Method Test::add()', (string) Callback::create($test, 'add')->toReflection() );

/**/Assert::same( $test, Callback::create($test)->getNative() );/**/
Assert::same( 'Test::__invoke', (string) Callback::create($test) );
Assert::same( 'Method Test::__invoke()', (string) Callback::create($test)->toReflection() );

/**/
$closure = function(){};
Assert::same( $closure, Callback::create($closure)->getNative() );
Assert::same( '{closure}', (string) Callback::create($closure) );
Assert::same( 'Function {closure}()', (string) Callback::create($closure)->toReflection() );
/**/
