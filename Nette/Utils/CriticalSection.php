<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Utils;

use Nette;



/**
 * Critical sections support.
 *
 * @author     David Grudl
 */
final class CriticalSection
{
	/** @var array */
	private static $criticalSections;



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * Enters the critical section, other threads are locked out.
	 * @return void
	 */
	public static function enter()
	{
		if (self::$criticalSections) {
			throw new Nette\InvalidStateException('Critical section has already been entered.');
		}
		// locking on Windows causes that a file seems to be empty
		$handle = substr(PHP_OS, 0, 3) === 'WIN'
			? @fopen(NETTE_DIR . '/lockfile', 'w')
			: @fopen(__FILE__, 'r'); // @ - file may not already exist

		if (!$handle) {
			throw new Nette\InvalidStateException("Unable initialize critical section.");
		}
		flock(self::$criticalSections = $handle, LOCK_EX);
	}



	/**
	 * Leaves the critical section, other threads can now enter it.
	 * @return void
	 */
	public static function leave()
	{
		if (!self::$criticalSections) {
			throw new Nette\InvalidStateException('Critical section has not been initialized.');
		}
		flock(self::$criticalSections, LOCK_UN);
		fclose(self::$criticalSections);
		self::$criticalSections = NULL;
	}

}
