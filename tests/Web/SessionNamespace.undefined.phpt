<?php

/**
 * Test: Nette\Web\SessionNamespace undefined property.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Web
 * @subpackage UnitTests
 */

/*use Nette\Web\Session;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



$session = new Session;
$namespace = $session->getNamespace('one');

Assert::false( isset($namespace->undefined) );

Assert::null( $namespace->undefined, 'Getting value of non-existent key' );

Assert::same( '', http_build_query($namespace->getIterator()) );
