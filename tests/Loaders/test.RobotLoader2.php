<h1>Nette::Loaders::RobotLoader & Caching test</h1>

<pre>
<?php


require_once '../../Nette/Environment.php';
require_once '../../Nette/Loaders/RobotLoader.php';
/*use Nette::Debug;*/
/*use Nette::Environment;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');
Environment::getCache()->offsetUnset('RobotLoader');


$loader = new /*Nette::Loaders::*/RobotLoader;
$loader->addDirectory(dirname(__FILE__));
$loader->register();

Debug::dump('class Nette::Debug successfully loaded');