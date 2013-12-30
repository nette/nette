<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Traversing helper. Internal class.
 *
 * @author     David Grudl
 * @internal
 */
class TokenIterator extends Nette\Object
{
	/** @var array */
	public $tokens;

	/** @var int */
	public $position = -1;

	/** @var array */
	public $ignored = array();


	/**
	 * @param array[]
	 */
	public function __construct(array $tokens)
	{
		$this->tokens = $tokens;
	}


	/**
	 * Returns current token.
	 * @return array|NULL
	 */
	public function currentToken()
	{
		return isset($this->tokens[$this->position])
			? $this->tokens[$this->position]
			: NULL;
	}


	/**
	 * Returns current token value.
	 * @return string|NULL
	 */
	public function currentValue()
	{
		return isset($this->tokens[$this->position])
			? $this->tokens[$this->position][Tokenizer::VALUE]
			: NULL;
	}


	/**
	 * Returns next token.
	 * @param  desired token
	 * @return array|NULL
	 */
	public function nextToken()
	{
		return $this->scan(func_get_args(), TRUE, TRUE); // onlyFirst, advance
	}


	/**
	 * Returns next token value.
	 * @param  desired token
	 * @return string|NULL
	 */
	public function nextValue()
	{
		return $this->scan(func_get_args(), TRUE, TRUE, TRUE); // onlyFirst, advance, strings
	}


	/**
	 * Returns all next tokens.
	 * @param  desired token
	 * @return array[]
	 */
	public function nextAll()
	{
		return $this->scan(func_get_args(), FALSE, TRUE); // advance
	}


	/**
	 * Returns all next tokens until it sees a token with the given value.
	 * @param  tokens
	 * @return array[]
	 */
	public function nextUntil($arg)
	{
		return $this->scan(func_get_args(), FALSE, TRUE, FALSE, TRUE); // advance, until
	}


	/**
	 * Returns concatenation of all next tokens.
	 * @param  desired token
	 * @return string
	 */
	public function joinAll()
	{
		return $this->scan(func_get_args(), FALSE, TRUE, TRUE); // advance, strings
	}


	/**
	 * Returns concatenation of all next tokens until it sees a token with the given value.
	 * @param  tokens
	 * @return string
	 */
	public function joinUntil($arg)
	{
		return $this->scan(func_get_args(), FALSE, TRUE, TRUE, TRUE); // advance, strings, until
	}


	/**
	 * Checks the current token.
	 * @param  token
	 * @return bool
	 */
	public function isCurrent($arg)
	{
		if (!isset($this->tokens[$this->position])) {
			return FALSE;
		}
		$args = func_get_args();
		$token = $this->tokens[$this->position];
		return in_array($token[Tokenizer::VALUE], $args, TRUE)
			|| (isset($token[Tokenizer::TYPE]) && in_array($token[Tokenizer::TYPE], $args, TRUE));
	}


	/**
	 * Checks the next token.
	 * @param  token
	 * @return bool
	 */
	public function isNext()
	{
		return (bool) $this->scan(func_get_args(), TRUE, FALSE); // onlyFirst
	}


	/**
	 * Checks the previous token.
	 * @param  token
	 * @return bool
	 */
	public function isPrev()
	{
		return (bool) $this->scan(func_get_args(), TRUE, FALSE, FALSE, FALSE, TRUE); // onlyFirst, prev
	}


	/**
	 * @return TokenIterator
	 */
	public function reset()
	{
		$this->position = -1;
		return $this;
	}


	protected function next()
	{
		$this->position++;
	}


	/**
	 * Looks for (first) (not) wanted tokens.
	 * @return mixed
	 */
	protected function scan($wanted, $onlyFirst, $advance, $strings = FALSE, $until = FALSE, $prev = FALSE)
	{
		$res = $onlyFirst ? NULL : ($strings ? '' : array());
		$pos = $this->position + ($prev ? -1 : 1);
		do {
			if (!isset($this->tokens[$pos])) {
				if (!$wanted && $advance && !$prev && $pos <= count($this->tokens)) {
					$this->next();
				}
				return $res;
			}

			$token = $this->tokens[$pos];
			$type = isset($token[Tokenizer::TYPE]) ? $token[Tokenizer::TYPE] : NULL;
			if (!$wanted || (in_array($token[Tokenizer::VALUE], $wanted, TRUE) || in_array($type, $wanted, TRUE)) ^ $until) {
				while ($advance && !$prev && $pos > $this->position) {
					$this->next();
				}

				if ($onlyFirst) {
					return $strings ? $token[Tokenizer::VALUE] : $token;
				} elseif ($strings) {
					$res .= $token[Tokenizer::VALUE];
				} else {
					$res[] = $token;
				}

			} elseif ($until || !in_array($type, $this->ignored, TRUE)) {
				return $res;
			}
			$pos += $prev ? -1 : 1;
		} while (TRUE);
	}

}
