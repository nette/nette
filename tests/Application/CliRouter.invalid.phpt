<?php

/**
 * Test: Nette\Application\CliRouter invalid argument
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Application
 * @subpackage UnitTests
 */

use Nette\Application\CliRouter,
	Nette\Web\HttpRequest;



require __DIR__ . '/../NetteTest/initialize.php';



$_SERVER['argv'] = 1;
$httpRequest = new HttpRequest;

$router = new CliRouter;
Assert::null( $router->match($httpRequest) );
