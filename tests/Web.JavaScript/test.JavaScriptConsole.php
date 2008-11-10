<?php

require_once '../../Nette/loader.php';
/*use Nette\Web\JavaScriptConsole;*/


echo "<xmp>\n";

// basic usage test
$js = new JavaScriptConsole;
$js->jQuery('table tr:eq(2) img')
		->css('z-index', 1000)
		->animate(array('top' => '100px'));

$js->fifteen->move(5, 6);

$js->fifteen->partialId = '';
$js->flush();


// method-set property test
try {
	$js = new JavaScriptConsole;
	$js->subitem->item->load('file.dat')->prop = 1;
	$js->flush();
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}


// method-double set property test
try {
	$js = new JavaScriptConsole;
	$item = $js->subitem->item->load('file.dat');
	$item->prop = 1;
	$item->prop = 1;
	$js->flush();
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}

// method-get property test
try {
	$js = new JavaScriptConsole;
	$item = $js->subitem->item->load('file.dat')->prop;
	$js->flush();
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}


// property 'this' test
$js = new JavaScriptConsole;
$js->this->prop = array(10, 20, 30);
$js->flush();


// 'var' test
$js = new JavaScriptConsole;
$js->var->prop = array(10, 20, 30);
$js->flush();


// assignment test
$js1 = new JavaScriptConsole;
$js2 = new JavaScriptConsole;
$js1->document->getElementById('#sidebar')->style->left = $js2->mouse->width;
$js1->flush();


// raw test
$js = new JavaScriptConsole;
$js->raw('if (i = 10) { m=', array(10, 20, 30), '; } else { ')->alert('Error')->raw('; }');
$js->flush();
