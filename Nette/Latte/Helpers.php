<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Latte;

use Nette;


/**
 * Latte helpers.
 *
 * @author     David Grudl
 */
class Helpers
{

	/**
	 * Removes unnecessary blocks of PHP code.
	 * @param  string
	 * @return string
	 */
	public static function optimizePhp($source, $lineLength = 80)
	{
		$res = $php = '';
		$lastChar = ';';
		$tokens = new \ArrayIterator(token_get_all($source));
		foreach ($tokens as $n => $token) {
			if (is_array($token)) {
				if ($token[0] === T_INLINE_HTML) {
					$lastChar = '';
					$res .= $token[1];

				} elseif ($token[0] === T_CLOSE_TAG) {
					$next = isset($tokens[$n + 1]) ? $tokens[$n + 1] : NULL;
					if (substr($res, -1) !== '<' && preg_match('#^<\?php\s*\z#', $php)) {
						$php = ''; // removes empty (?php ?), but retains ((?php ?)?php

					} elseif (is_array($next) && $next[0] === T_OPEN_TAG && (!isset($tokens[$n + 2][1]) || $tokens[$n + 2][1] !== 'xml')) { // remove ?)(?php
						if (!strspn($lastChar, ';{}:/')) {
							$php .= $lastChar = ';';
						}
						if (substr($next[1], -1) === "\n") {
							$php .= "\n";
						}
						$tokens->next();

					} elseif ($next) {
						$res .= preg_replace('#;?(\s)*\z#', '$1', $php) . $token[1]; // remove last semicolon before ?)
						if (strlen($res) - strrpos($res, "\n") > $lineLength
							&& (!is_array($next) || strpos($next[1], "\n") === FALSE)
						) {
							$res .= "\n";
						}
						$php = '';

					} else { // remove last ?)
						if (!strspn($lastChar, '};')) {
							$php .= ';';
						}
					}

				} elseif ($token[0] === T_ELSE || $token[0] === T_ELSEIF) {
					if ($tokens[$n + 1] === ':' && $lastChar === '}') {
						$php .= ';'; // semicolon needed in if(): ... if() ... else:
					}
					$lastChar = '';
					$php .= $token[1];

				} elseif ($token[0] === T_OPEN_TAG && $token[1] === '<?' && isset($tokens[$n+1][1]) && $tokens[$n+1][1] === 'xml') {
					$lastChar = '';
					$php .= '<<?php ?>?';
					for ($tokens->next(); $tokens->valid(); $tokens->next()) {
						$token = $tokens->current();
						$php .= is_array($token) ? $token[1] : $token;
						if ($token[0] === T_CLOSE_TAG) {
							break;
						}
					}

				} else {
					if (!in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_OPEN_TAG))) {
						$lastChar = '';
					}
					$php .= $token[1];
				}
			} else {
				$php .= $lastChar = $token;
			}
		}
		return $res . $php;
	}

}
