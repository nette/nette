<h1>Nette\Paginator test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette\Paginator;*/
/*use Nette\Debug;*/


$paginator = new Paginator;
$paginator->itemCount = 7;
$paginator->itemsPerPage = 6;
$paginator->base = 0;


$paginator->page = 3;

echo "page:\n";
Debug::dump($paginator->page);

echo "pageCount:\n";
Debug::dump($paginator->pageCount);

echo "firstPage:\n";
Debug::dump($paginator->firstPage);

echo "lastPage:\n";
Debug::dump($paginator->lastPage);

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

echo "firstPage:\n";
Debug::dump($paginator->firstPage);

echo "lastPage:\n";
Debug::dump($paginator->lastPage);

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

echo "firstPage:\n";
Debug::dump($paginator->firstPage);

echo "lastPage:\n";
Debug::dump($paginator->lastPage);

echo "offset:\n";
Debug::dump($paginator->offset);

echo "countdownOffset:\n";
Debug::dump($paginator->countdownOffset);

echo "length:\n";
Debug::dump($paginator->length);
