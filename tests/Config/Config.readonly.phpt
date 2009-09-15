<?php

/**
 * Test: Config readonly.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

/*use Nette\Config\Config;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$config = Config::fromFile('config1.ini', 'development', NULL);

try {
	section("check read-only config:");
	$config->freeze();
	$config->database->adapter = 'new value';
} catch (Exception $e) {
	dump( $e );
}

try {
	section("check read-only clone:");
	$dolly = clone $config;
	$dolly->database->adapter = 'works good';
	unset($dolly);
} catch (Exception $e) {
	dump( $e );
}

try {
	section("check read-only clone II:");
	$dolly = unserialize(serialize($config));
	$dolly->database->adapter = 'works good';
} catch (Exception $e) {
	dump( $e );
}



__halt_compiler();

------EXPECT------
==> check read-only config:

Exception InvalidStateException: Cannot modify a frozen object '%ns%Config'.

==> check read-only clone:

==> check read-only clone II:

Exception InvalidStateException: Cannot modify a frozen object '%ns%Config'.
