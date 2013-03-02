<?php

/**
 * Test: Nette\DI\ContainerBuilder and generated factories with inject methods.
 *
 * @author     Filip Procházka
 * @package    Nette\DI
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';


class Ipsum
{

}


class Lorem
{

	public $ipsum;



	/**
	 * @param Ipsum $ipsum
	 */
	public function injectIpsum(Ipsum $ipsum)
	{
		$this->ipsum = $ipsum;
	}

}


interface LoremFactory
{
	/** @return Lorem */
	function create();
}


$builder = new DI\ContainerBuilder;
$builder->addDefinition('lorem')
	->setImplement('LoremFactory');

$builder->addDefinition('ipsum')
	->setClass('Ipsum');

// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->getService('lorem') instanceof LoremFactory );

$lorem = $container->getService('lorem')->create();

Assert::true( $lorem instanceof Lorem );
Assert::true( $lorem->ipsum instanceof Ipsum );
