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
class Helpers extends Tracy\Helpers
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

}
