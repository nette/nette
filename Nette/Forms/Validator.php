<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Forms;

use Nette,
	Nette\Utils\Strings,
	Nette\Utils\Validators;


/**
 * Common validators.
 *
 * @author     David Grudl
 */
class Validator extends Nette\Object
{
	/** @var array */
	public static $messages = array(
		Form::PROTECTION => 'Please submit this form again (security token has expired).',
		Form::EQUAL => 'Please enter %s.',
		Form::FILLED => 'Please complete mandatory field.',
		Form::MIN_LENGTH => 'Please enter a value of at least %d characters.',
		Form::MAX_LENGTH => 'Please enter a value no longer than %d characters.',
		Form::LENGTH => 'Please enter a value between %d and %d characters long.',
		Form::EMAIL => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::INTEGER => 'Please enter a numeric value.',
		Form::FLOAT => 'Please enter a numeric value.',
		Form::RANGE => 'Please enter a value between %d and %d.',
		Form::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		Form::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
		Nette\Forms\Controls\SelectBox::VALID => 'Please select a valid option.',
	);


	public static function formatMessage(Rule $rule, $withValue = TRUE)
	{
		$message = $rule->message;
		if ($message instanceof Nette\Utils\Html) {
			return $message;

		} elseif ($message === NULL && is_string($rule->operation) && isset(static::$messages[$rule->operation])) {
			$message = static::$messages[$rule->operation];

		} elseif ($message == NULL) { // intentionally ==
			trigger_error("Missing validation message for control '{$rule->control->name}'.", E_USER_WARNING);
		}

		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : NULL);
		}

		$message = preg_replace_callback('#%(name|label|value|\d+\$[ds]|[ds])#', function($m) use ($rule, $withValue) {
			static $i = -1;
			switch ($m[1]) {
				case 'name': return $rule->control->getName();
				case 'label': return $rule->control->translate($rule->control->caption);
				case 'value': return $withValue ? $rule->control->getValue() : $m[0];
				default:
					$args = is_array($rule->arg) ? $rule->arg : array($rule->arg);
					$i = (int) $m[1] ? $m[1] - 1 : $i + 1;
					return isset($args[$i]) ? ($args[$i] instanceof IControl ? ($withValue ? $args[$i]->getValue() : "%$i") : $args[$i]) : '';
			}
		}, $message);
		return $message;
	}


	/********************* default validators ****************d*g**/


	/**
	 * Is control's value equal with second parameter?
	 * @return bool
	 */
	public static function validateEqual(IControl $control, $arg)
	{
		$value = $control->getValue();
		foreach ((is_array($value) ? $value : array($value)) as $val) {
			foreach ((is_array($arg) ? $arg : array($arg)) as $item) {
				if ((string) $val === (string) $item) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}


	/**
	 * Is control filled?
	 * @return bool
	 */
	public static function validateFilled(IControl $control)
	{
		return $control->isFilled();
	}


	/**
	 * Is control valid?
	 * @return bool
	 */
	public static function validateValid(IControl $control)
	{
		return !$control->getRules()->validate();
	}


	/**
	 * Is a control's value number in specified range?
	 * @return bool
	 */
	public static function validateRange(IControl $control, $range)
	{
		return Validators::isInRange($control->getValue(), $range);
	}


	/**
	 * Count/length validator. Range is array, min and max length pair.
	 * @return bool
	 */
	public static function validateLength(IControl $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}
		$value = $control->getValue();
		return Validators::isInRange(is_array($value) ? count($value) : Strings::length($value), $range);
	}


	/**
	 * Has control's value minimal count/length?
	 * @return bool
	 */
	public static function validateMinLength(IControl $control, $length)
	{
		return static::validateLength($control, array($length, NULL));
	}


	/**
	 * Is control's value count/length in limit?
	 * @return bool
	 */
	public static function validateMaxLength(IControl $control, $length)
	{
		return static::validateLength($control, array(NULL, $length));
	}


	/**
	 * Has been button pressed?
	 * @return bool
	 */
	public static function validateSubmitted(Controls\SubmitButton $control)
	{
		return $control->isSubmittedBy();
	}


	/**
	 * Is control's value valid email address?
	 * @return bool
	 */
	public static function validateEmail(IControl $control)
	{
		return Validators::isEmail($control->getValue());
	}


	/**
	 * Is control's value valid URL?
	 * @return bool
	 */
	public static function validateUrl(IControl $control)
	{
		return Validators::isUrl($control->getValue()) || Validators::isUrl('http://' . $control->getValue());
	}


	/** @deprecated */
	public static function validateRegexp(IControl $control, $regexp)
	{
		trigger_error('Validator REGEXP is deprecated; use PATTERN instead (which is matched against the entire value and is case sensitive).', E_USER_DEPRECATED);
		return (bool) Strings::match($control->getValue(), $regexp);
	}


	/**
	 * Matches control's value regular expression?
	 * @return bool
	 */
	public static function validatePattern(IControl $control, $pattern)
	{
		return (bool) Strings::match($control->getValue(), "\x01^($pattern)\\z\x01u");
	}


	/**
	 * Is a control's value decimal number?
	 * @return bool
	 */
	public static function validateInteger(IControl $control)
	{
		return Validators::isNumericInt($control->getValue());
	}


	/**
	 * Is a control's value float number?
	 * @return bool
	 */
	public static function validateFloat(IControl $control)
	{
		return Validators::isNumeric(str_replace(array(' ', ','), array('', '.'), $control->getValue()));
	}


	/**
	 * Is file size in limit?
	 * @return bool
	 */
	public static function validateFileSize(Controls\UploadControl $control, $limit)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if ($file->getSize() > $limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * Has file specified mime type?
	 * @return bool
	 */
	public static function validateMimeType(Controls\UploadControl $control, $mimeType)
	{
		$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
		foreach (static::toArray($control->getValue()) as $file) {
			$type = strtolower($file->getContentType());
			if (!in_array($type, $mimeTypes, TRUE) && !in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * Is file image?
	 * @return bool
	 */
	public static function validateImage(Controls\UploadControl $control)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if (!$file->isImage()) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * @return array
	 */
	private static function toArray($value)
	{
		return $value instanceof Nette\Http\FileUpload ? array($value) : (array) $value;
	}

}
