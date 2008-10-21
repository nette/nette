<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette
 * @version    $Id$
 */

require_once dirname(__FILE__) . '/Loaders/NetteLoader.php';


define('NETTE_DIR', dirname(__FILE__));

$__loader = new /*Nette::Loaders::*/NetteLoader;
$__loader->base = NETTE_DIR;
$__loader->register();
unset($__loader);
