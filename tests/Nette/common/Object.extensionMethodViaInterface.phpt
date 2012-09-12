<?php

/**
 * Test: Nette\Object extension method via interface.
 *
 * @author     David Grudl
 * @package    Nette
 */

use Nette\Reflection;



require __DIR__ . '/../bootstrap.php';



interface IFirst
{}

interface ISecond extends IFirst
{}

class TestClass extends Nette\Object implements ISecond
{
	public $foo = 'Hello', $bar = 'World';
}



function IFirst_join(ISecond $that, $separator)
{
	return __METHOD__ . ' says ' . $that->foo . $separator . $that->bar;
}



function ISecond_join(ISecond $that, $separator)
{
	return __METHOD__ . ' says ' . $that->foo . $separator . $that->bar;
}



Reflection\ClassType::from('IFirst')->setExtensionMethod('join', 'IFirst_join');
Reflection\ClassType::from('ISecond')->setExtensionMethod('join', 'ISecond_join');

$obj = new TestClass;
Assert::same( 'ISecond_join says Hello*World', $obj->join('*') );
