<?php

/**
 * Test: Nette\Loaders\RobotLoader basic usage.
 *
 * @author     David Grudl
 * @package    Nette\Loaders
 */

use Nette\Loaders\RobotLoader,
	Nette\Caching\Storages\DevNullStorage;


require __DIR__ . '/../bootstrap.php';


$loader = new RobotLoader;
$loader->setCacheStorage(new DevNullStorage);
$loader->addDirectory(__DIR__ . '/files');
$loader->addDirectory(__DIR__ . '/files/'); // purposely doubled
$loader->addDirectory(__DIR__ . '/file/interface.php'); // as file
if (PHP_VERSION_ID >= 50400) $loader->addDirectory(__DIR__ . '/file/trait.php');
$loader->addDirectory(__DIR__ . '/files.robots');
$loader->register();

Assert::false( class_exists('ConditionalClass') );   // files/conditional.class.php
Assert::true( interface_exists('TestInterface') );   // file/interface.php
if (PHP_VERSION_ID >= 50400) Assert::true( trait_exists('TestTrait') ); // file/trait.php

Assert::true( class_exists('TestClass') );           // files/namespaces1.php
Assert::true( class_exists('MySpace1\TestClass1') ); // files/namespaces1.php
Assert::true( class_exists('MySpace2\TestClass2') ); // files/namespaces2.php
Assert::true( class_exists('MySpace3\TestClass3') ); // files/namespaces2.php

Assert::false( class_exists('Disallowed1') );   // files.robots\disallowed1\class.php
Assert::false( class_exists('Disallowed2') );   // files.robots\disallowed2\class.php
Assert::false( class_exists('Disallowed3') );   // files.robots\subdir\class.php
Assert::true( class_exists('Allowed1') );       // files.robots\subdir\allowed.php
Assert::false( class_exists('Disallowed4') );   // files.robots\subdir\disallowed4\class.php
Assert::false( class_exists('Disallowed5') );   // files.robots\subdir\subdir2\disallowed5\class.php
Assert::false( class_exists('Disallowed6') );   // files.robots\subdir\subdir2\class.php
Assert::true( class_exists('Allowed2') );       // files.robots\subdir\subdir2\allowed.php
