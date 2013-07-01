<?php

/**
 * Test: Nette\Forms default rendering GET form.
 *
 * @author     David Grudl
 * @package    Nette\Forms
 */


require __DIR__ . '/../bootstrap.php';


$form = new Nette\Forms\Form;
$form->setMethod('GET');
$form->setAction('link?a=b&c[]=d');
$form->addHidden('userid');
$form->addSubmit('submit', 'Send');

$form->fireEvents();

Assert::match( '<form action="link" method="get">
	<div><input type="hidden" name="a" value="b" /><input type="hidden" name="c[]" value="d" /></div>

<table>
<tr>
	<th></th>

	<td><input type="submit" name="_submit" class="button" id="frm-submit" value="Send" /></td>
</tr>
</table>

<div><input type="hidden" name="userid" id="frm-userid" value="" /><!--[if IE]><input type=IEbug disabled style="display:none"><![endif]--></div>
</form>', $form->__toString(TRUE) );

Assert::same( 'link?a=b&c[]=d', $form->getAction() );
