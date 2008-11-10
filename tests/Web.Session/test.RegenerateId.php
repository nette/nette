<?php ob_start() ?>
<h1>Nette\Web\Session RegenerateId test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Web\Session;*/

$session = new Session;

echo "\nSetting id:\n";

$session->setId('myid123');
$session->regenerateId();

$id = $session->getId();
echo "id = $id\n\n";


$session->start();

try {
	echo "\nSetting id after start:\n";
	$session->setId($id);
	echo "ERROR\n";

} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
