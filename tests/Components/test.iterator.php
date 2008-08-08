<h1>Nette::Component iterator test</h1>

<pre>
<?php
require_once '../../Nette/loader.php';

/*use Nette::Component;*/
/*use Nette::ComponentContainer;*/
/*use Nette::Forms::Button;*/
/*use Nette::Debug;*/

class ComponentX extends Component
{
}

$c = new ComponentContainer(NULL, 'top');

$c->addComponent(new ComponentContainer, 'one');
$c->addComponent(new ComponentX, 'two');
$c->addComponent(new Button('label'), 'button1');

$c->getComponent('one')->addComponent(new ComponentX, 'inner');
$c->getComponent('one')->addComponent(new ComponentContainer, 'inner2');
$c->getComponent('one')->getComponent('inner2')->addComponent(new Button('label'), 'button2');



echo "Normal:\n";
foreach ($c->getComponents() as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";



echo "Filter:\n";
foreach ($c->getComponents(FALSE, 'Nette::Forms::Button') as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";



echo "RecursiveIteratorIterator:\n";
foreach (new RecursiveIteratorIterator($c->getComponents(), 1) as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";



echo "Recursive:\n";
foreach ($c->getComponents(TRUE) as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";


echo "Recursive CHILD_FIRST:\n";
foreach ($c->getComponents(-1) as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";


echo "Recursive & filter I:\n";
foreach ($c->getComponents(TRUE, 'Nette::Forms::Button') as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";


echo "Recursive & filter II:\n";
foreach ($c->getComponents(TRUE, 'Nette::ComponentContainer') as $name => $component) {
	echo "$name [$component->class]\n";
}
echo "\n\n";
