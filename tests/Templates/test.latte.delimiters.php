<h1>Nette\Templates\LatteFilter test</h1>

<?php
require_once '../../Nette/loader.php';

/*use Nette\Debug;*/
/*use Nette\Environment;*/
/*use Nette\Templates\Template;*/

Environment::setVariable('tempDir', dirname(__FILE__) . '/tmp');

$template = new Template;
$template->setFile(dirname(__FILE__) . '/templates/latte-delimiters.phtml');
$template->registerFilter(new /*Nette\Templates\*/CurlyBracketsFilter);
$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');
$template->people = array('John', 'Mary', 'Paul');

$template->render();
