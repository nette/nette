<?php

/**
 * Test: Nette\Database Basic operations.
 *
 * @author     Jakub Vrana
 * @author     Jan Skrasek
 * @package    Nette\Caching
 * @subpackage UnitTests
 */

use Nette\Database;



require __DIR__ . '/../bootstrap.php';



$dbFile = __DIR__ . '/software.s3db';
$connection = new Database\Connection("sqlite:$dbFile");
