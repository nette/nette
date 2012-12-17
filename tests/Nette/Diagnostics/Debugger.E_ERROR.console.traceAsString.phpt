<?php

/**
 * Test: Nette\Diagnostics\Debugger FatalErrorException::getTraceAsString() and reflection xdebug override.
 *
 * @author     Filip Procházka
 * @package    Nette\Diagnostics
 */

use Nette\Diagnostics\Debugger;



require __DIR__ . '/../bootstrap.php';


header('Content-Type: text/plain');
Debugger::enable(FALSE);


$exception = create_new();
$exception->getTrace();
$exception->getTraceAsString();

Assert::match("exception 'Nette\\FatalErrorException' with message 'message' in %a%
Stack trace:
#0 %a%: exception_trace('message')
#1 %a%: create_new()
#2 {main}
", $exception->__toString());


$ref = new \ReflectionProperty('Exception', 'trace');
$ref->setAccessible(TRUE);
$ref->setValue($exception, array());
Assert::match("exception 'Nette\\FatalErrorException' with message 'message' in %a%
Stack trace:
#0 {main}
", $exception->__toString());


$ref->setValue($exception, array(array(
	'file' => __FILE__,
	'line' => __LINE__,
	'function' => 'create_new',
	'args' => array()
)));
Assert::match("exception 'Nette\\FatalErrorException' with message 'message' in %a%
Stack trace:
#0 %a%: create_new()
#1 {main}
", $exception->__toString());


$ref->setValue($exception, array(array(
	'file' => __FILE__,
	'line' => __LINE__,
	'function' => 'run',
	'class' => 'Nette\Application\Application',
	'type' => '->',
	'args' => array()
)));
Assert::match("exception 'Nette\\FatalErrorException' with message 'message' in %a%
Stack trace:
#0 %a%: Nette\\Application\\Application->run()
#1 {main}
", $exception->__toString());



function create_new()
{
	return exception_trace("message");
}

function exception_trace($msg)
{
	return new \Nette\FatalErrorException($msg, 0, E_ERROR, __FILE__, __LINE__, NULL);
}
