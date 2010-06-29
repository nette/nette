<?php

/**
 * Test: Nette\Config\ConfigAdapterIni section.
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

use Nette\Config\Config;



require __DIR__ . '/../initialize.php';



T::note("Load INI");
$config = Config::fromFile('config2.ini');
T::dump( $config );

T::note("Save INI");
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');


T::note("Save section to INI");
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');


T::note("Load section from INI");
$config = Config::fromFile('config2.ini', 'development');
T::dump( $config );

T::note("Save INI");
$config->display_errors = true;
$config->html_errors = false;
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');



__halt_compiler() ?>
