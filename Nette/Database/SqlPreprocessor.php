<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Database;

use Nette;


/**
 * SQL preprocessor.
 *
 * @author     David Grudl
 */
class SqlPreprocessor extends Nette\Object
{
	/** @var Connection */
	private $connection;

	/** @var ISupplementalDriver */
	private $driver;

	/** @var array of input parameters */
	private $params;

	/** @var array of parameters to be processed by PDO */
	private $remaining;

	/** @var int */
	private $counter;

	/** @var string values|assoc|multi|select|union */
	private $arrayMode;

	/** @var array */
	private $arrayModes;


	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->driver = $connection->getSupplementalDriver();
		$this->arrayModes = array(
			'INSERT' => $this->driver->isSupported(ISupplementalDriver::SUPPORT_MULTI_INSERT_AS_SELECT) ? 'select' : 'values',
			'REPLACE' => 'values',
			'UPDATE' => 'assoc',
			'WHERE' => 'and',
			'HAVING' => 'and',
			'ORDER BY' => 'order',
			'GROUP BY' => 'order',
		);
	}


	/**
	 * @param  array
	 * @return array of [sql, params]
	 */
	public function process($params)
	{
		$this->params = $params;
		$this->counter = 0;
		$this->remaining = array();
		$this->arrayMode = 'assoc';
		$res = array();

		while ($this->counter < count($params)) {
			$param = $params[$this->counter++];

			if (($this->counter === 2 && count($params) === 2) || !is_scalar($param)) {
				$res[] = $this->formatValue($param);
			} else {
				$res[] = Nette\Utils\Strings::replace(
					$param,
					'~\'.*?\'|".*?"|\?|\b(?:INSERT|REPLACE|UPDATE|WHERE|HAVING|ORDER BY|GROUP BY)\b|/\*.*?\*/|--[^\n]*~si',
					array($this, 'callback')
				);
			}
		}

		return array(implode(' ', $res), $this->remaining);
	}


	/** @internal */
	public function callback($m)
	{
		$m = $m[0];
		if ($m[0] === "'" || $m[0] === '"' || $m[0] === '/' || $m[0] === '-') { // string or comment
			return $m;

		} elseif ($m === '?') { // placeholder
			if ($this->counter >= count($this->params)) {
				throw new Nette\InvalidArgumentException('There are more placeholders than passed parameters.');
			}
			return $this->formatValue($this->params[$this->counter++]);

		} else { // command
			$this->arrayMode = $this->arrayModes[strtoupper($m)];
			return $m;
		}
	}


	private function formatValue($value)
	{
		if (is_string($value)) {
			if (strlen($value) > 20) {
				$this->remaining[] = $value;
				return '?';

			} else {
				return $this->connection->quote($value);
			}

		} elseif (is_int($value)) {
			return (string) $value;

		} elseif (is_float($value)) {
			return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');

		} elseif (is_bool($value)) {
			return $this->driver->formatBool($value);

		} elseif ($value === NULL) {
			return 'NULL';

		} elseif ($value instanceof Table\IRow) {
			return $value->getPrimary();

		} elseif (is_array($value) || $value instanceof \Traversable) {
			$vx = $kx = array();

			if ($value instanceof \Traversable) {
				$value = iterator_to_array($value);
			}

			if (isset($value[0])) { // non-associative; value, value, value
				foreach ($value as $v) {
					if (is_array($v) && isset($v[0])) { // no-associative; (value), (value), (value)
						$vx[] = '(' . $this->formatValue($v) . ')';
					} else {
						$vx[] = $this->formatValue($v);
					}
				}
				if ($this->arrayMode === 'union') {
					return implode(' ', $vx);
				}
				return implode(', ', $vx);

			} elseif ($this->arrayMode === 'values') { // (key, key, ...) VALUES (value, value, ...)
				$this->arrayMode = 'multi';
				foreach ($value as $k => $v) {
					$kx[] = $this->driver->delimite($k);
					$vx[] = $this->formatValue($v);
				}
				return '(' . implode(', ', $kx) . ') VALUES (' . implode(', ', $vx) . ')';

			} elseif ($this->arrayMode === 'select') { // (key, key, ...) SELECT value, value, ...
				$this->arrayMode = 'union';
				foreach ($value as $k => $v) {
					$kx[] = $this->driver->delimite($k);
					$vx[] = $this->formatValue($v);
				}
				return '(' . implode(', ', $kx) . ') SELECT ' . implode(', ', $vx);

			} elseif ($this->arrayMode === 'assoc') { // key=value, key=value, ...
				foreach ($value as $k => $v) {
					if (substr($k, -1) === '=') {
						$k2 = $this->driver->delimite(substr($k, 0, -2));
						$vx[] = $k2 . '=' . $k2 . ' ' . substr($k, -2, 1) . ' ' . $this->formatValue($v);
					} else {
						$vx[] = $this->driver->delimite($k) . '=' . $this->formatValue($v);
					}
				}
				return implode(', ', $vx);

			} elseif ($this->arrayMode === 'multi') { // multiple insert (value, value, ...), ...
				foreach ($value as $v) {
					$vx[] = $this->formatValue($v);
				}
				return '(' . implode(', ', $vx) . ')';

			} elseif ($this->arrayMode === 'union') { // UNION ALL SELECT value, value, ...
				foreach ($value as $v) {
					$vx[] = $this->formatValue($v);
				}
				return 'UNION ALL SELECT ' . implode(', ', $vx);

			} elseif ($this->arrayMode === 'and') { // (key [operator] value) AND ...
				foreach ($value as $k => $v) {
					$k = $this->driver->delimite($k);
					if (is_array($v)) {
						$vx[] = $v ? ($k . ' IN (' . $this->formatValue(array_values($v)) . ')') : '1=0';
					} else {
						$v = $this->formatValue($v);
						$vx[] = $k . ($v === 'NULL' ? ' IS ' : ' = ') . $v;
					}
				}
				return $value ? '(' . implode(') AND (', $vx) . ')' : '1=1';

			} elseif ($this->arrayMode === 'order') { // key, key DESC, ...
				foreach ($value as $k => $v) {
					$vx[] = $this->driver->delimite($k) . ($v > 0 ? '' : ' DESC');
				}
				return implode(', ', $vx);
			}

		} elseif ($value instanceof \DateTime || $value instanceof \DateTimeInterface) {
			return $this->driver->formatDateTime($value);

		} elseif ($value instanceof SqlLiteral) {
			$this->remaining = array_merge($this->remaining, $value->getParameters());
			return $value->__toString();

		} else {
			$this->remaining[] = $value;
			return '?';
		}
	}

}
