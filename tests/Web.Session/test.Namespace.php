<?php ob_start() ?>
<h1>Nette\Web\Session Namespace test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Web\Session;*/
/*use Nette\Debug;*/

echo "\nGetting namespace:\n";

$session = new Session;

$s = $session->getNamespace('default');
echo get_class($s);


try {
	echo "\nHas namespace trees? (should be FALSE)\n";
	Debug::dump($session->hasNamespace('trees'));

	echo "\nHas namespace trees II? (should be FALSE)\n";
	$s = $session->getNamespace('trees');
	Debug::dump($session->hasNamespace('trees'));

	echo "\nHas namespace trees II? (should be TRUE)\n";
	$s->cherry = 'bing';
	Debug::dump($session->hasNamespace('trees'));

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
