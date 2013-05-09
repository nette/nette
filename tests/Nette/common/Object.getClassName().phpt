<?php

/**
 * Test: Nette\Object class name.
 *
 * @author     Filip Procházka
 * @package    Nette
 */




require __DIR__ . '/../bootstrap.php';



class TestClass extends Nette\Object
{
}


$obj = new TestClass;
Assert::same( 'TestClass', $obj->getClassName() );
Assert::same( 'TestClass', TestClass::getClassName() );
