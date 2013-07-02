<?php

/**
 * Test: Nette\Utils\Json::encode()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Json;


require __DIR__ . '/../bootstrap.php';


Assert::same( '"ok"', Json::encode('ok') );


Assert::exception(function() {
	Json::encode(array("bad utf\xFF"));
}, 'Nette\Utils\JsonException', '%a?%Invalid UTF-8 sequence%a?%');


Assert::exception(function() {
	$arr = array('recursive');
	$arr[] = & $arr;
	Json::encode($arr);
}, 'Nette\Utils\JsonException', '%a?%ecursion detected');
