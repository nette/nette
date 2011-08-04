<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette;

use Nette;



/**
 * DateTime with serialization and timestamp support for PHP 5.2.
 *
 * @author     David Grudl
 */
class DateTime extends \DateTime
{
	/** minute in seconds */
	const MINUTE = 60;

	/** hour in seconds */
	const HOUR = 3600;

	/** day in seconds */
	const DAY = 86400;

	/** week in seconds */
	const WEEK = 604800;

	/** average month in seconds */
	const MONTH = 2629800;

	/** average year in seconds */
	const YEAR = 31557600;

	/** date format */
	const W3C_DATE_FORMAT = 'Y-m-d';

	/** time format */
	const W3C_TIME_FORMAT = 'H:i:s';



	/**
	 * DateTime object factory.
	 * @param  string|int|\DateTime
	 * @return DateTime
	 */
	public static function from($time)
	{
		if ($time instanceof \DateTime) {
			return clone $time;

		} elseif (is_numeric($time)) {
			if ($time <= self::YEAR) {
				$time += time();
			}
			return new static(date('Y-m-d H:i:s', $time));

		} else { // textual or NULL
			return new static($time);
		}
	}



	/**
	 * Formats time to W3C date format
	 * @param string|int|\DateTime
	 * @return string
	 */
	public static function toW3cDateFormat($time)
	{
		$dateTime = self::from($time);
		return $dateTime->format(self::W3C_DATE_FORMAT);
	}



	/**
	 * Formats time to W3C datetime format
	 * @param string|int|\DateTime
	 * @return string
	 */
	public static function toW3cDateTimeFormat($time)
	{
		$dateTime = self::from($time);

		// Get timezone offset
		$offset = 'Z';
		if (($delta = $dateTime->getOffset()) !== false) {
			$offset = '';

			$offsetInHours = $delta / 3600;
			$helper = explode('.', $offsetInHours);

			// Hours
			$hours = abs($helper[0]);
			if ($hours < 10) {
				$hours = '0' . $hours;
			}
			$offset .= ($helper[0] >= 0 ? '+' : '-') . $hours;

			// Mins
			if (isset($helper[1])) { // e.g. +04:30
				$mins = $helper[1] * 6;
				if ($mins < 10) {
					$mins = '0' . $mins;
				}
			} else {
				$mins = '00';
			}

			// Final offset string
			$offset .= ':' . $mins;
		}

		return $dateTime->format(self::W3C_DATE_FORMAT) . 'T' . $dateTime->format(self::W3C_TIME_FORMAT) . $offset;
	}



	/*5.2*
	public static function __set_state($state)
	{
		return new self($state['date'], new \DateTimeZone($state['timezone']));
	}



	public function __sleep()
	{
		$this->fix = array($this->format('Y-m-d H:i:s'), $this->getTimezone()->getName());
		return array('fix');
	}



	public function __wakeup()
	{
		$this->__construct($this->fix[0], new \DateTimeZone($this->fix[1]));
		unset($this->fix);
	}



	public function getTimestamp()
	{
		return (int) $this->format('U');
	}



	public function setTimestamp($timestamp)
	{
		return $this->__construct(
			gmdate('Y-m-d H:i:s', $timestamp + $this->getOffset()),
			new \DateTimeZone($this->getTimezone()->getName()) // simply getTimezone() crashes in PHP 5.2.6
		);
	}
	*/

}
