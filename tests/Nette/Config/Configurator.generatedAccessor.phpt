<?php

/**
 * Test: Nette\Config\Configurator: generated services accessors.
 *
 * @author     David Grudl
 * @package    Nette\Config
 */

use Nette\Config\Configurator;



require __DIR__ . '/../bootstrap.php';



class Lorem
{
}

interface ILoremAccessor
{
	/** @return Lorem */
	function get();
}



$configurator = new Configurator;
$configurator->setTempDirectory(TEMP_DIR);
$container = $configurator->addConfig('files/config.generatedAccessor.neon')
	->createContainer();

Assert::true( $container->getService('lorem') instanceof Lorem );
Assert::true( $container->getService('lorem') !== $container->getService('lorem2') );

Assert::true( $container->getService('one') instanceof ILoremAccessor );
Assert::true( $container->getService('one')->get() === $container->getService('lorem') );

Assert::true( $container->getService('two') instanceof ILoremAccessor );
Assert::true( $container->getService('two')->get() === $container->getService('lorem') );

Assert::true( $container->getService('three') instanceof ILoremAccessor );
Assert::true( $container->getService('three')->get() === $container->getService('lorem2') );

Assert::true( $container->getService('four') instanceof ILoremAccessor );
Assert::true( $container->getService('four')->get() === $container->getService('lorem') );
