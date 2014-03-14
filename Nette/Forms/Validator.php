<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
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
		Form::NOT_EQUAL => 'This value should not be %s.',
		Form::FILLED => 'This field is required.',
		Form::BLANK => 'This field should be blank.',
		Form::MIN_LENGTH => 'Please enter at least %d characters.',
		Form::MAX_LENGTH => 'Please enter no more than %d characters.',
		Form::LENGTH => 'Please enter a value between %d and %d characters long.',
		Form::EMAIL => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::INTEGER => 'Please enter a valid integer.',
		Form::FLOAT => 'Please enter a valid number.',
		Form::MIN => 'Please enter a value greater than or equal to %d.',
		Form::MAX => 'Please enter a value less than or equal to %d.',
		Form::RANGE => 'Please enter a value between %d and %d.',
		Form::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		Form::MAX_POST_SIZE => 'The uploaded data exceeds the limit of %d bytes.',
		Form::MIME_TYPE => 'The uploaded file is not in the expected format.',
		Form::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
		Nette\Forms\Controls\SelectBox::VALID => 'Please select a valid option.',
	);


	public static function formatMessage(Rule $rule, $withValue = TRUE)
	{
		$message = $rule->message;
		if ($message instanceof Nette\Utils\Html) {
			return $message;

		} elseif ($message === NULL && is_string($rule->validator) && isset(static::$messages[$rule->validator])) {
			$message = static::$messages[$rule->validator];

		} elseif ($message == NULL) { // intentionally ==
			trigger_error("Missing validation message for control '{$rule->control->getName()}'.", E_USER_WARNING);
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
					continue 2;
				}
			}
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Is control's value not equal with second parameter?
	 * @return bool
	 */
	public static function validateNotEqual(IControl $control, $arg)
	{
		return !static::validateEqual($control, $arg);
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
	 * Is control not filled?
	 * @return bool
	 */
	public static function validateBlank(IControl $control)
	{
		return !$control->isFilled();
	}


	/**
	 * Is control valid?
	 * @return bool
	 */
	public static function validateValid(IControl $control)
	{
		return $control->getRules()->validate();
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
	 * Is a control's value number greater than or equal to the specified minimum?
	 * @return bool
	 */
	public static function validateMin(IControl $control, $minimum)
	{
		return Validators::isInRange($control->getValue(), array($minimum, NULL));
	}


	/**
	 * Is a control's value number less than or equal to the specified maximum?
	 * @return bool
	 */
	public static function validateMax(IControl $control, $maximum)
	{
		return Validators::isInRange($control->getValue(), array(NULL, $maximum));
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
		if (Validators::isUrl($value = $control->getValue())) {
			return TRUE;

		} elseif (Validators::isUrl($value = "http://$value")) {
			$control->setValue($value);
			return TRUE;
		}
		return FALSE;
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
		if (Validators::isNumericInt($value = $control->getValue())) {
			if (!is_float($tmp = $value * 1)) { // bigint leave as string
				$control->setValue($tmp);
			}
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * Is a control's value float number?
	 * @return bool
	 */
	public static function validateFloat(IControl $control)
	{
		$value = str_replace(array(' ', ','), array('', '.'), $control->getValue());
		if (Validators::isNumeric($value)) {
			$control->setValue((float) $value);
			return TRUE;
		}
		return FALSE;
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
