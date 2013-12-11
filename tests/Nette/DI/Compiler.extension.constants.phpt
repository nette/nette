<?php

/**
 * Test: Nette\DI\Compiler: constants in config.
 *
 * @author     David Grudl
 * @package    Nette\DI
 */

use Nette\DI,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$loader = new DI\Config\Loader;
$compiler = new DI\Compiler;
$compiler->addExtension('constants', new Nette\DI\Extensions\ConstantsExtension);
$code = $compiler->compile($loader->load('files/compiler.extension.constants.neon'), 'Container', 'Nette\DI\Container');

file_put_contents(TEMP_DIR . '/code.php', "<?php\n\n$code");
require TEMP_DIR . '/code.php';

$container = new Container;
$container->initialize();

Assert::same( "hello", a );
Assert::same( "WORLD", A );
