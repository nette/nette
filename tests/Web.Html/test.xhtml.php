<?php

require_once '../../Nette/loader.php';
/*use Nette::Web::Html;*/


echo "<xmp>\n";

$el = Html::el('img')->src('image.gif')->alt('');
echo $el, "\n";
echo $el->startTag(), "\n";
echo $el->endTag(), "\n";

$el = Html::el('img')->src('image.gif')->alt('')->setText(NULL)->setText('any content');
echo $el, "\n";
echo $el->startTag(), "\n";
echo $el->endTag(), "\n";

$el = Html::el('div');
$el->style[] = 'text-align:right';
$el->style[] = NULL;
$el->style[] = 'background-color: blue';
$el->class[] = 'one';
$el->class[] = NULL;
$el->class[] = 'two';

echo $el, "\n";

$el->style = NULL;
$el->style['text-align'] = 'left';
$el->style['background-color'] = 'green';
echo $el, "\n";

$el->style = 'float:left';
$el->class = 'three';
$el->lang = '';
$el->title = '0';
$el->checked = TRUE;
$el->selected = FALSE;
$el->name = 'testname';
$el->setName('span');
echo $el, "\n";

echo Html::el('p')->setText('Hello &ndash; World'), "\n";
echo Html::el('p')->setText('Hello &ndash; World', TRUE), "\n";

// add
$el = Html::el('ul');
$el->create('li')->setText('one');
$el->add( Html::el('li')->setText('two') )->class('hello');
echo $el, "\n";


// container
$el = Html::el(NULL);
$el->add( Html::el('p')->setText('one') );
$el->add( Html::el('p')->setText('two') );
echo $el, "\n";

// get child
echo 'Child1: ', (int) isset($el[1]), "\n";
echo 'Child2: ', (int) isset($el[2]), "\n";
$child = $el[1];
echo $child, "\n";

//parent
echo $el[1]->getParent()->count(), "\n";

// add child
try {
	$el->add($child);
} catch (Exception $e) {
	echo $e->getMessage(), "\n";
}

$child = clone $el[1];
$el->add($child);

// remove child
$child = $el[0];
unset($el[0]);
$el[] = $child;
echo $el, "\n";



// email obfuscate
echo $el = Html::el('a')->href('mailto:dave@example.com');
echo "\n";


// href with query
echo $el = Html::el('a')->href('file.php', array('a' => 10));
echo "\n";
