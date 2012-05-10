<?php

/**
 * Test: Nette\DI\ContainerBuilder and anonymous services.
 *
 * @author     David Grudl
 * @package    Nette\DI
 * @subpackage UnitTests
 */

use Nette\DI;



require __DIR__ . '/../bootstrap.php';


class One
{
}

class Two extends \Nette\Object
{
	/** @var One @inject */
	protected $one;

	public function hasOne() {
		return (bool) $this->one;
	}
}



$builder = new DI\ContainerBuilder;
$builder->addDefinition('one')
	->setClass('One');

$builder->addDefinition('two')
	->setClass('Two');


$two = new Two;
$two->injOne(new One);
Assert::true($two->hasOne());


$code = (string) $builder->generateClass();
file_put_contents(TEMP_DIR . '/code.php', "<?php\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

//dump($container->two);

//Assert::true( $container->getByType('Service') instanceof Service );
//Assert::true( $container->getByType('stdClass') instanceof stdClass );
