<?php

/**
 * Test: Nette\Database Calling __toString().
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Database
 * @subpackage UnitTests
 */

require_once dirname(__FILE__) . '/connect.inc.php';



$primaryKeyValue = (string) $connection->table('application')->get(2);
Assert::equal('2', $primaryKeyValue);
