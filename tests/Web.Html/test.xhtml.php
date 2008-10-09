<?php

require_once '../../Nette/loader.php';
/*use Nette::Web::Html;*/


echo "<xmp>\n";

$el = Html::el('img')->src('image.gif')->alt('');
echo $el, "\n";
echo $el->startTag(), "\n";
echo $el->endTag(), "\n\n";

$el = Html::el('img')->src('image.gif')->alt('')->setText(NULL)->setText('any content');
echo $el, "\n";
echo $el->startTag(), "\n";
echo $el->endTag(), "\n\n";

$el = Html::el('div');
$el->style[] = 'text-align:right';
$el->style[] = NULL;
$el->style[] = 'background-color: blue';
$el->class[] = 'one';
$el->class[] = NULL;
$el->class[] = 'two';

echo $el, "\n\n";

$el->style = NULL;
$el->style['text-align'] = 'left';
$el->style['background-color'] = 'green';
echo $el, "\n\n";

$el->style = 'float:left';
$el->class = 'three';
$el->lang = '';
$el->title = '0';
$el->checked = TRUE;
$el->selected = FALSE;
$el->name = 'testname';
$el->setName('span');
echo $el, "\n\n";

echo Html::el('p')->setText('Hello &ndash; World'), "\n";
echo Html::el('p')->setText('Hello &ndash; World', TRUE), "\n\n";
echo Html::el('p')->setHtml('Hello &ndash; World'), "\n\n";

// add
$el = Html::el('ul');
$el->create('li')->setText('one');
$el->add( Html::el('li')->setText('two') )->class('hello');
echo $el, "\n\n";

// with indentation
echo $el->render(2), "\n\n";


// container
$el = Html::el(NULL);
$el->add( Html::el('p')->setText('one') );
$el->add( Html::el('p')->setText('two') );
echo $el, "\n\n";

// get child
echo 'Child1: ', (int) isset($el[1]), "\n";
echo 'Child2: ', (int) isset($el[2]), "\n";
$child = $el[1];
echo $child, "\n\n";


// iterator
$el = Html::el('select');
$el->create('optgroup')->label('Main')->create('option')->setText('sub one')->create('option')->setText('sub two');
$el->create('option')->setText('Item');
echo $el, "\n\n";

echo "Iterator:\n";
foreach ($el as $name => $child) {
	echo $child instanceof Html ? $child->getName() : "'$child'", "\n";
}
echo "\n";

echo "Deep iterator:\n";
foreach ($el->getIterator(TRUE) as $name => $child) {
	echo $child instanceof Html ? $child->getName() : "'$child'", "\n";
}
echo "\n";

// email obfuscate
echo $el = Html::el('a')->href('mailto:dave@example.com');
echo "\n\n";


// href with query
echo $el = Html::el('a')->href('file.php', array('a' => 10));
echo "\n\n";


// special creating
echo $el = Html::el('a lang=cs href="#" title="" selected')->setText('click');
echo "\n\n";
