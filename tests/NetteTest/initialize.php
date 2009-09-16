<?php

/**
 * Test initialization and helpers.
 *
 * @author     David Grudl
 * @package    Nette\Test
 */

require dirname(__FILE__) . '/NetteTestCase.php';

require dirname(__FILE__) . '/NetteTestHelpers.php';

require dirname(__FILE__) . '/../../Nette/loader.php';



NetteTestHelpers::startup();



/**
 * Dumps information about a variable in readable format.
 * @param  mixed  variable to dump
 * @param  string
 * @return mixed  variable itself or dump
 */
function dump($var, $message = NULL)
{
	if ($message) {
		echo $message . (preg_match('#[.:?]$#', $message) ? ' ' : ': ');
	}

	NetteTestHelpers::dump($var, 0);
	echo "\n";
	return $var;
}



/**
 * Writes new message.
 * @param  string
 * @return void
 */
function output($message = NULL)
{
	echo $message ? "$message\n\n" : "===\n\n";
}
