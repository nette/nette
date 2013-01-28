<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\PhpGenerator;

use Nette;



/**
 * Class property description.
 *
 * @author     David Grudl
 *
 * @method Property setName(string $name)
 * @method Property setValue(mixed $value)
 * @method Property setStatic(bool $on)
 * @method Property setVisibility(string $access)
 * @method Property addDocument(string $doc)
 */
class Property extends Nette\Object
{
	/** @var string */
	public $name;

	/** @var mixed */
	public $value;

	/** @var bool */
	public $static;

	/** @var string  public|protected|private */
	public $visibility = 'public';

	/** @var array of string */
	public $documents = array();


	/** @return Property */
	public static function from(\ReflectionProperty $from)
	{
	$prop = new static;
	$prop->name = $from->getName();
	$defaults = $from->getDeclaringClass()->getDefaultProperties();
	$prop->value = isset($defaults[$from->name]) ? $defaults[$from->name] : NULL;
	$prop->static = $from->isStatic();
	$prop->visibility = $from->isPrivate() ? 'private' : ($from->isProtected() ? 'protected' : 'public');
	$prop->documents = preg_replace('#^\s*\* ?#m', '', trim($from->getDocComment(), "/* \r\n"));
	return $prop;
	}



	public function __call($name, $args)
	{
	return Nette\ObjectMixin::callProperty($this, $name, $args);
	}

}
