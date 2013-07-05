<?php

/**
 * Test: Nette\Latte\Macros\FormMacros: GET form
 *
 * @author     Filip Procházka
 * @package    Nette\Latte
 */

use Nette\Latte\Macros\FormMacros;
use Nette\Application\UI\Form;


require __DIR__ . '/../bootstrap.php';

$form = new Form;
$form->setMethod('GET');
$form->action = 'http://example.com/?do=foo-submit#toc';

ob_start();
FormMacros::renderFormBegin($form, array());
Assert::same('<form action="http://example.com/#toc" method="get">', ob_get_clean());

ob_start();
FormMacros::renderFormEnd($form);
Assert::match('<div><input type="hidden" name="do" value="foo-submit"><!--[if IE]><input type=IEbug disabled style="display:none"><![endif]--></div>
</form>
', ob_get_clean());
