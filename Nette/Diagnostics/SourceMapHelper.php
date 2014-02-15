<?php

namespace Nette\Diagnostics;


/**
 * Resolves source maps
 */
class SourceMapHelper
{

	/**
	 * Converts line number in compiled file into line number in source file (e.g. for latte templates)
	 *
	 * @param  string
	 * @param  int
	 * @return null|array  [ sourceFile, sourceLine ] or null if not found
	 */
	public static function sourceMapLookup($originalFile, $originalLine)
	{
		$data = file_get_contents($originalFile);
		if (!preg_match('~^// source file: (.+)$~m', $data, $match)) {
			return;
		}
		$sourceFile = trim($match[1]);

		if (!preg_match('~// source map: ([^\s]+)~', $data, $match)) {
			return;
		}
		$map = json_decode($match[1]);

		$compiledLine = $originalLine - substr_count($data, "\n", 0, strpos($data, $match[0])) - 1; // adjust line for source map beginning (some lines before the mapped file + 1 line of source map definition)

		// find closest key
		$compiledLines = array();
		foreach (array_keys((array) $map) as $line) {
			$compiledLines[$line] = abs($line - $compiledLine);
		}
		asort($compiledLines);
		$closestLine = key($compiledLines);

		return array($sourceFile, reset($map->$closestLine));
	}

}


