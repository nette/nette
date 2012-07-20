<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Reflection;

use Nette,
	Nette\Utils\Strings;



/**
 * Param annotation implementation
 *
 * @todo: support of use
 *
 * @author     Filip Procházka
 */
class ParamAnnotation extends Annotation
{

	/** @var string */
	public $value;

	/** @var string */
	public $type;

	/** @var string */
	public $name;

	/** @var string */
	public $description;

	/** @var string */
	public $generic;



	/**
	 * @param array
	 */
	public function __construct(array $values)
	{
		$this->value = $value = $values['value'];

		$re = '(\\\\?' . AnnotationsParser::RE_IDENTIFIER . ')([^<>]*)(\<\\\\?' . AnnotationsParser::RE_IDENTIFIER . '\>)?(.*)';
		if ($generic = Strings::match($value, '~^' . $re . '$~i')) {
			list(, $this->type, $a, $this->generic, $b) = $generic;
			$value = trim($a . $b);
			$this->generic = trim($this->generic, '<>');
		}

		if ($m = Strings::match($value, '~^(\$[^\s]+)?(.*)~i')) {
			list(, $this->name, $this->description) = $m;
			$this->name = ltrim($this->name, '$');
			$this->description = trim($this->description);
		}

		$this->type = ltrim($this->type, '\\');
		$this->generic = ltrim($this->generic, '\\');
	}



	/**
	 * @internal
	 * @param Parameter $pr
	 *
	 * @return ParamAnnotation
	 */
	public function complete(Parameter $pr)
	{
		if (!$this->name) {
			$this->name = $pr->getName();

		} elseif ($this->name !== $pr->getName()) {
			trigger_error("Parameter name in annotation @param is not corresponding to parameter $pr.", E_USER_NOTICE);
		}

		if (!$this->type && ($className = $pr->getClassName())) {
			$this->type = $className;
		}

		return $this;
	}

}
