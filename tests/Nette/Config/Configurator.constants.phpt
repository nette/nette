<?php

/**
 * Test: Nette\Config\Configurator: constants in config.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Lorem
{
	const DOLOR_SIT = 10;

	public $arg;

	function __construct($arg)
	{
		$this->arg = $arg;
	}
}

define('MY_CONSTANT_TEST', "one");

$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig(__DIR__ . '/files/config.constants.neon', Configurator::NONE)
	->createContainer();

Assert::same( "one", $container->ipsum->arg );
Assert::same( Lorem::DOLOR_SIT, $container->sit->arg );
Assert::same( "MY_FAILING_CONSTANT_TEST", $container->consectetur->arg );

Assert::error(function () use ($container) {
	$container->amet->arg;
}, E_NOTICE, "Use of undefined constant MY_FAILING_CONSTANT_TEST - assumed 'MY_FAILING_CONSTANT_TEST'");
