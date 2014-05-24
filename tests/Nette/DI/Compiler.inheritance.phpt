<?php

/**
 * Test: Nette\DI\Compiler: multiple service inhertance
 * @package    Nette\DI
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';



class BaseService
{
	private $private;

	function setPrivate($private) {
		$this->private = $private;
	}

	function getPrivate() {
		return $this->private;
	}
}


class ChildService extends BaseService
{}


class SubChildService extends ChildService
{}


class SecondChildService extends ChildService
{}



define('PRIVATE_VALUE', 'foo.bar');


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$code = $compiler->compile($loader->load('files/compiler.inheritance.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;

Assert::same(PRIVATE_VALUE, $container->getService('base')->getPrivate());
Assert::same(PRIVATE_VALUE, $container->getService('child')->getPrivate());
Assert::same(PRIVATE_VALUE, $container->getService('subchild')->getPrivate());
Assert::same(PRIVATE_VALUE, $container->getService('secchild')->getPrivate());
