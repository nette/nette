<?php

/**
 * Test: Nette\Reflection\ClassType:is()
 *
 * @author     David Grudl
 * @package    Nette\Reflection
 */

use Nette\Reflection;


require __DIR__ . '/../bootstrap.php';


class Foo
{
}

abstract class Bar extends Foo implements Countable
{
}

interface Countable2 extends Countable
{
}


Assert::true( Reflection\ClassType::from('Bar')->is('Bar') );
Assert::true( Reflection\ClassType::from('Bar')->is('\Bar') );
Assert::true( Reflection\ClassType::from('Bar')->is('Foo') );
Assert::true( Reflection\ClassType::from('Bar')->is('\Foo') );
Assert::true( Reflection\ClassType::from('Bar')->is('Countable') );
Assert::true( Reflection\ClassType::from('Bar')->is('\Countable') );

Assert::true( Reflection\ClassType::from('Foo')->is('Foo') );
Assert::false( Reflection\ClassType::from('Foo')->is('Bar') );
Assert::false( Reflection\ClassType::from('Foo')->is('Countable') );

Assert::true( Reflection\ClassType::from('Countable')->is('Countable') );
Assert::false( Reflection\ClassType::from('Countable')->is('Bar') );

Assert::true( Reflection\ClassType::from('Countable2')->is('Countable2') );
Assert::true( Reflection\ClassType::from('Countable2')->is('Countable') );
