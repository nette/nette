<?php

/**
 * Test: Nette\Config\ConfigAdapterIni
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette\Config
 * @subpackage UnitTests
 */

/*use Nette\Config\Config;*/



require dirname(__FILE__) . '/../NetteTest/initialize.php';



output("Load INI");
$config = Config::fromFile('config1.ini');
dump( $config );
dump( $config->toArray(), "toArray()" );

output("Save INI");
$config->save('tmp/cfg.ini');
readfile('tmp/cfg.ini');

output("Save section to INI");
$config->save('tmp/cfg.ini', 'mysection');
readfile('tmp/cfg.ini');



__halt_compiler();
