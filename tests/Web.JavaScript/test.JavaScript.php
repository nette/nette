<?php

require_once '../../Nette/loader.php';
/*use Nette::Web::JavaScript;*/


echo "<xmp>\n";

// basic usage test
$js = new JavaScript;
$js->jQuery('table tr:eq(2) img')
		->css('z-index', 1000)
		->animate(array('top' => '100px'));
echo $js, "\n\n";

$js = new JavaScript;
$js->fifteen->move(5, 6);
echo $js, "\n\n";


$js = new JavaScript;
$js->fifteen->partialId = '';
echo $js, "\n\n";


// method-set property test
try {
	$js = new JavaScript;
	$js->subitem->item->load('file.dat')->prop = 1;
	echo $js, "\n\n";
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}


// method-double set property test
try {
	$js = new JavaScript;
	$item = $js->subitem->item->load('file.dat');
	$item->prop = 1;
	$item->prop = 1;
	echo $js, "\n\n";
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}

// method-get property test
try {
	$js = new JavaScript;
	$item = $js->subitem->item->load('file.dat')->prop;
	echo $js, "\n\n";
} catch (Exception $e) {
	echo get_class($e) . ': ' . $e->getMessage(), "\n\n";
}


// property 'this' test
$js = new JavaScript;
$js->this->prop = array(10, 20, 30);
echo $js, "\n\n";


// 'var' test
$js = new JavaScript;
$js->var->prop = array(10, 20, 30);
echo $js, "\n\n";


// assignment test
$js1 = new JavaScript;
$js2 = new JavaScript;
$js1->document->getElementById('#sidebar')->style->left = $js2->mouse->width;
echo $js1, "\n\n";


// raw test
$js = new JavaScript;
$js->raw('if (i = 10) { m=', array(10, 20, 30), '; } else { ')->alert('Error')->raw('; }');
echo $js, "\n\n";


// raw test II
$js = new JavaScript;
$js->jQuery('p img')
		->animate(array('top' => '100px'))
		->queue( new JavaScript('function () { alert("animation gone"); }') );

echo $js, "\n\n";
