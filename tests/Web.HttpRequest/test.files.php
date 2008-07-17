<h1>Nette::Web::HttpRequest uploaded files test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::Web::HttpRequest;*/

$_FILES = require '_FILES.php';

$request = new HttpRequest;

Debug::$maxDepth = 0;
Debug::dump($request->getFiles());

// warning:
// Debug::dump(isset($request->files['file0'])); -> FALSE
// Debug::dump(isset($request->files['file1'])); -> TRUE
// Debug::dump(isset($request->files['file1']['a'])); -> throws error
