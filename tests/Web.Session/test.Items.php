<?php ob_start() ?>
<h1>Nette::Web::Session Items test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::Session;*/

$session = /*Nette::*/Environment::getSession();

echo "\nGetting non-existent key:\n";

try {
	$s = $session->getNamespace('default');
	$s->tree = 'fig';
	$dog = $s->dog;
	Debug::dump($dog);

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
