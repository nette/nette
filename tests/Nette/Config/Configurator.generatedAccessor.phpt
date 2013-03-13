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

Assert::true( $container->lorem instanceof Lorem );
Assert::true( $container->lorem !== $container->lorem2 );

Assert::true( $container->one instanceof ILoremAccessor );
Assert::true( $container->one->get() === $container->lorem );

Assert::true( $container->two instanceof ILoremAccessor );
Assert::true( $container->two->get() === $container->lorem );

Assert::true( $container->three instanceof ILoremAccessor );
Assert::true( $container->three->get() === $container->lorem2 );

Assert::true( $container->four instanceof ILoremAccessor );
Assert::true( $container->four->get() === $container->lorem );
