<?php

/**
 * Test: Nette\Utils\Json::decode()
 *
 * @author     David Grudl
 * @package    Nette\Utils
 */

use Nette\Utils\Json;



require __DIR__ . '/../bootstrap.php';



Assert::same( "ok", Json::decode('"ok"') );
Assert::null( Json::decode('') );
Assert::null( Json::decode('null') );
Assert::null( Json::decode('NULL') );


Assert::equal( (object) array('a' => 1), Json::decode('{"a":1}') );
Assert::same( array('a' => 1), Json::decode('{"a":1}', Json::FORCE_ARRAY) );



Assert::exception(function() {
	Json::decode('{');
}, 'Nette\Utils\JsonException', 'Syntax error, malformed JSON');



Assert::exception(function() {
	Json::decode('{}}');
}, 'Nette\Utils\JsonException', 'Syntax error, malformed JSON');



Assert::exception(function() {
	Json::decode("\x00");
}, 'Nette\Utils\JsonException', 'Unexpected control character found');
