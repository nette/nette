<h1>Nette\Templates\TemplateHelpers test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Templates\TemplateHelpers;*/

Debug::enable();




// TemplateHelpers::bytes
echo "TemplateHelpers::bytes(0.1)\n";
Debug::dump(TemplateHelpers::bytes(0.1));

echo "TemplateHelpers::bytes(-1024 * 1024 * 1050)\n";
Debug::dump(TemplateHelpers::bytes(-1024 * 1024 * 1050));

echo "TemplateHelpers::bytes(1e19)\n";
Debug::dump(TemplateHelpers::bytes(1e19));
