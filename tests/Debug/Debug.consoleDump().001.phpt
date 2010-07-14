<?php

/**
 * Test: Nette\Debug::consoleDump()
 *
 * @author     David Grudl
 * @category   Nette
 * @package    Nette
 * @subpackage UnitTests
 */

use Nette\Debug;



require __DIR__ . '/../initialize.php';



Debug::$consoleMode = FALSE;
Debug::$productionMode = FALSE;

header('Content-Type: text/html');


$arr = array(10, 20.2, TRUE, FALSE, NULL, 'hello', array('key1' => 'val1', 'key2' => TRUE), (object) array('key1' => 'val1', 'key2' => TRUE));

Debug::consoleDump($arr);

end($arr)->key1 = 'changed'; // make post-change

Debug::consoleDump('<a href="#">test</a>', 'String');



__halt_compiler() ?>

------EXPECT------


<script type="text/javascript">
%A%
_netteConsole.document.body.innerHTML = _netteConsole.document.body.innerHTML + "\t\r\n\t<table>\r\n\t\t\t<tr class=\"even\">\r\n\t\t<th>0<\/th>\r\n\t\t<td><pre class=\"dump\">10\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"odd\">\r\n\t\t<th>1<\/th>\r\n\t\t<td><pre class=\"dump\">20.2\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"even\">\r\n\t\t<th>2<\/th>\r\n\t\t<td><pre class=\"dump\">TRUE\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"odd\">\r\n\t\t<th>3<\/th>\r\n\t\t<td><pre class=\"dump\">FALSE\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"even\">\r\n\t\t<th>4<\/th>\r\n\t\t<td><pre class=\"dump\">NULL\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"odd\">\r\n\t\t<th>5<\/th>\r\n\t\t<td><pre class=\"dump\">\"hello\" (5)\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"even\">\r\n\t\t<th>6<\/th>\r\n\t\t<td><pre class=\"dump\"><a href='#' onclick='return !netteToggle(this)'><span>array<\/span>(2) <abbr>&#x25bc;<\/abbr> <\/a><code>{\n   \"key1\" => \"val1\" (4)\n   \"key2\" => TRUE\n}<\/code>\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<tr class=\"odd\">\r\n\t\t<th>7<\/th>\r\n\t\t<td><pre class=\"dump\"><a href='#' onclick='return !netteToggle(this)'><span>stdClass<\/span>(2) <abbr>&#x25bc;<\/abbr> <\/a><code>{\n   \"key1\" => \"val1\" (4)\n   \"key2\" => TRUE\n}<\/code>\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<\/table>\r\n\t\t<h2>String<\/h2>\r\n\t\r\n\t<table>\r\n\t\t\t<tr class=\"even\">\r\n\t\t<th><\/th>\r\n\t\t<td><pre class=\"dump\">\"&lt;a href=\"#\"&gt;test&lt;\/a&gt;\" (20)\n<\/pre>\n<\/td>\r\n\t<\/tr>\r\n\t\t<\/table>\r\n";
%A%
</script>
