<?php

/**
 * Test: Nette\Http\Session::regenerateId()
 *
 * @author     David Grudl
 * @package    Nette\Http
 * @subpackage UnitTests
 */

use Nette\Http\Session;



require __DIR__ . '/../bootstrap.php';


ini_set('session.save_path', TEMP_DIR);



$container = id(new Nette\Config\Configurator)->setTempDirectory(TEMP_DIR)->createContainer();

$session = $container->session;
$path = rtrim(ini_get('session.save_path'), '/\\') . '/sess_';

$session->start();
$oldId = $session->getId();
Assert::true( is_file($path . $oldId) );
$ref = & $_SESSION['var'];
$ref = 10;

$session->regenerateId();
$newId = $session->getId();
Assert::true( $newId != $oldId );
Assert::true( is_file($path . $newId) );

$ref = 20;
Assert::same( 20, $_SESSION['var'] );
