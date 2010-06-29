<?php

/**
 * Test: Nette\Config\ConfigAdapterIni
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../initialize.php';



T::note("Load INI");
$config = Config::fromFile('config1.ini');
T::dump( $config );
T::dump( $config->toArray(), "toArray()" );

T::note("Save INI");
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');

T::note("Save section to INI");
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');



__halt_compiler() ?>
