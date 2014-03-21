<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Diagnostics;

use Nette,
	Tracy;


/**
 * @deprecated
 */
class Debugger extends Tracy\Debugger
{
	/** @deprecated */
	public static $consoleMode;

	/** @deprecated */
	public static $consoleColors;

	/** @deprecated @var BlueScreen*/
	public static $blueScreen;

	/** @deprecated @var Logger */
	public static $logger;

	/** @deprecated @var FireLogger */
	public static $fireLogger;

	/** @deprecated @var Bar */
	public static $bar;


	/**
	 * Enables displaying or logging errors and exceptions.
	 * @param  mixed         production, development mode, autodetection or IP address(es) whitelist.
	 * @param  string        error log directory; enables logging in production mode, FALSE means that logging is disabled
	 * @param  string        administrator email; enables email sending in production mode
	 * @return void
	 */
	public static function enable($mode = NULL, $logDirectory = NULL, $email = NULL)
	{
		parent::enable($mode, $logDirectory, $email);
		self::$blueScreen = self::getBlueScreen();
		self::$bar = self::getBar();
		self::$logger = self::getLogger();
		self::$fireLogger = self::getFireLogger();
		self::$consoleColors = & Tracy\Dumper::$terminalColors;
	}


	public static function addPanel(IBarPanel $panel, $id = NULL)
	{
		return self::getBar()->addPanel($panel, $id);
	}

}
