<?php

/**
 * Test: Nette\DI\ContainerBuilder and inject methods.
 *
 * @author     David Grudl
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

	public function injectIpsum(Ipsum $ipsum)
	{
		$this->ipsum = $ipsum;
	}

	public function inject($val)
	{
	}

}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('lorem')
	->setClass('Lorem')
	->addSetup('inject', array(123));

$builder->addDefinition('ipsum')
	->setClass('Ipsum');

// run-time
$code = implode('', $builder->generateClasses());
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::true( $container->getService('lorem') instanceof Lorem );
Assert::true( $container->getService('lorem')->ipsum instanceof Ipsum );
