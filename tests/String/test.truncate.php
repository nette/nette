<h1>Nette::String truncate method test</h1>

<pre>
<?php

require_once '../../Nette/loader.php';

/*use Nette::Debug;*/
/*use Nette::String;*/


iconv_set_encoding('internal_encoding', 'UTF-8');
$s = 'Řekněte, jak se (dnes) máte?';

for ($i = -1; $i < 33; $i++) {
	echo "Length $i: '", String::truncate($s, $i), "'\n\n";
}
