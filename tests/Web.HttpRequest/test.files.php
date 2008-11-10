<h1>Nette\Web\HttpRequest uploaded files test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Web\HttpRequest;*/

$_FILES = require '_FILES.php';

$request = new HttpRequest;

Debug::$maxDepth = 0;
Debug::dump($request->getFiles());

// is set?:
Debug::dump(isset($request->files['file0'])); // FALSE
Debug::dump(isset($request->files['file1'])); // TRUE
Debug::dump($request->getFile('file1', 'a')); // isset($request->files['file1']['a']) throws error


$file = $request->getFile('file3', 'y', 'z');
echo "Before move:\n";
echo $file, "\n";
try {
	$file->move('new.loc');
} catch (Exception $e) {
	echo get_class($e), ': ', $e->getMessage(), "\n\n";
}
echo "After move:\n";
echo $file, "\n";
