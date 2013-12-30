<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Diagnostics;

use Nette;


/**
 * Rendering helpers for Debugger.
 *
 * @author     David Grudl
 */
class Helpers
{

	/**
	 * Returns link to editor.
	 * @return Nette\Utils\Html
	 */
	public static function editorLink($file, $line)
	{
		if (Debugger::$editor && is_file($file)) {
			$dir = dirname(strtr($file, '/', DIRECTORY_SEPARATOR));
			$base = isset($_SERVER['SCRIPT_FILENAME']) ? dirname(dirname(strtr($_SERVER['SCRIPT_FILENAME'], '/', DIRECTORY_SEPARATOR))) : dirname($dir);
			if (substr($dir, 0, strlen($base)) === $base) {
				$dir = '...' . substr($dir, strlen($base));
			}
			return Nette\Utils\Html::el('a')
				->href(strtr(Debugger::$editor, array('%file' => rawurlencode($file), '%line' => $line)))
				->title("$file:$line")
				->setHtml(htmlSpecialChars(rtrim($dir, DIRECTORY_SEPARATOR), ENT_IGNORE) . DIRECTORY_SEPARATOR . '<b>' . htmlSpecialChars(basename($file), ENT_IGNORE) . '</b>' . ($line ? ":$line" : ''));
		} else {
			return Nette\Utils\Html::el('span')->setText($file . ($line ? ":$line" : ''));
		}
	}


	public static function findTrace(array $trace, $method, & $index = NULL)
	{
		$m = explode('::', $method);
		foreach ($trace as $i => $item) {
			if (isset($item['function']) && $item['function'] === end($m)
				&& isset($item['class']) === isset($m[1])
				&& (!isset($item['class']) || $item['class'] === $m[0] || $m[0] === '*' || is_subclass_of($item['class'], $m[0]))
			) {
				$index = $i;
				return $item;
			}
		}
	}


	public static function fixStack($exception)
	{
		if (function_exists('xdebug_get_function_stack')) {
			$stack = array();
			foreach (array_slice(array_reverse(xdebug_get_function_stack()), 2, -1) as $row) {
				$frame = array(
					'file' => $row['file'],
					'line' => $row['line'],
					'function' => isset($row['function']) ? $row['function'] : '*unknown*',
					'args' => array(),
				);
				if (!empty($row['class'])) {
					$frame['type'] = isset($row['type']) && $row['type'] === 'dynamic' ? '->' : '::';
					$frame['class'] = $row['class'];
				}
				$stack[] = $frame;
			}
			$ref = new \ReflectionProperty('Exception', 'trace');
			$ref->setAccessible(TRUE);
			$ref->setValue($exception, $stack);
		}
		return $exception;
	}


	/** @deprecated */
	public static function htmlDump($var)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Nette\Diagnostics\Dumper::toHtml() instead.', E_USER_DEPRECATED);
		return Dumper::toHtml($var);
	}

	/** @deprecated */
	public static function clickableDump($var)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Nette\Diagnostics\Dumper::toHtml() instead.', E_USER_DEPRECATED);
		return Dumper::toHtml($var);
	}

	/** @deprecated */
	public static function textDump($var)
	{
		trigger_error(__METHOD__ . '() is deprecated; use Nette\Diagnostics\Dumper::toText() instead.', E_USER_DEPRECATED);
		return Dumper::toText($var);
	}

}
