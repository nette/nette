<h1>Nette::Loaders::NetteLoader test</h1>

<pre>
<?php

require_once '../../Nette/Loaders/NetteLoader.php';
/*use Nette::Debug;*/

$loader = new /*Nette::Loaders::*/NetteLoader;
$loader->register();

Debug::dump('class Nette::Debug successfully loaded');
