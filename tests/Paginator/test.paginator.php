<h1>Nette\Paginator test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Paginator;*/
/*use Nette\Debug;*/


$paginator = new Paginator;
$paginator->itemCount = 7;
$paginator->itemsPerPage = 6;


$paginator->page = 3;

echo "page:\n";
Debug::dump($paginator->page);

echo "pageCount:\n";
Debug::dump($paginator->pageCount);

echo "offset:\n";
Debug::dump($paginator->offset);

echo "countdownOffset:\n";
Debug::dump($paginator->countdownOffset);

echo "length:\n";
Debug::dump($paginator->length);


echo "\n";
$paginator->page = -1;

echo "page:\n";
Debug::dump($paginator->page);

echo "offset:\n";
Debug::dump($paginator->offset);

echo "countdownOffset:\n";
Debug::dump($paginator->countdownOffset);

echo "length:\n";
Debug::dump($paginator->length);


echo "\n";
$paginator->itemsPerPage = 7;

echo "page:\n";
Debug::dump($paginator->page);

echo "pageCount:\n";
Debug::dump($paginator->pageCount);

echo "offset:\n";
Debug::dump($paginator->offset);

echo "countdownOffset:\n";
Debug::dump($paginator->countdownOffset);

echo "length:\n";
Debug::dump($paginator->length);




echo "\n";
$paginator->itemCount = -1;

echo "page:\n";
Debug::dump($paginator->page);

echo "pageCount:\n";
Debug::dump($paginator->pageCount);

echo "offset:\n";
Debug::dump($paginator->offset);

echo "countdownOffset:\n";
Debug::dump($paginator->countdownOffset);

echo "length:\n";
Debug::dump($paginator->length);


echo "\n";
$paginator->itemCount = 170;
$paginator->itemsPerPage = 5;
var_dump($paginator->getSteps(3));
