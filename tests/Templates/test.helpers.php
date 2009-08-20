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



// TemplateHelpers::date
echo "TemplateHelpers::date(NULL)\n";
Debug::dump(TemplateHelpers::date(NULL));

echo "TemplateHelpers::date(timestamp)\n";
Debug::dump(TemplateHelpers::date(254400000));

echo "TemplateHelpers::date(string)\n";
Debug::dump(TemplateHelpers::date('1978-05-05'));

echo "TemplateHelpers::date(DateTime)\n";
Debug::dump(TemplateHelpers::date(new DateTime('1978-05-05')));

echo "TemplateHelpers::date(timestamp, format)\n";
Debug::dump(TemplateHelpers::date(254400000, 'Y-m-d'));

echo "TemplateHelpers::date(string, format)\n";
Debug::dump(TemplateHelpers::date('1212-09-26', 'Y-m-d'));

echo "TemplateHelpers::date(DateTime, format)\n";
Debug::dump(TemplateHelpers::date(new DateTime('1212-09-26'), 'Y-m-d'));
