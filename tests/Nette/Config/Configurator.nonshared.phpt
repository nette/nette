<?php

/**
 * Test: Nette\Config\Configurator: nonshared services factories.
 *
 * @author     David Grudl
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Ipsum
{
}

class Lorem
{
}


$configurator = new Configurator;
$configurator->setCacheDirectory(TEMP_DIR);
$container = $configurator->loadConfig('files/config.nonshared.neon', FALSE);

Assert::false( $container->hasService('lorem') );
Assert::true( method_exists($container, 'createLorem') );
