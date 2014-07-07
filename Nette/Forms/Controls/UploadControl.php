<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette,
	Nette\Http;


/**
 * Text box and browse button that allow users to select a file to upload to the server.
 *
 * @author     David Grudl
 */
class UploadControl extends BaseControl
{

	/**
	 * @param  string  label
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'file';
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof Nette\Forms\Form) {
			if ($form->getMethod() !== Nette\Forms\Form::POST) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
		parent::attached($form);
	}


	/**
	 * Sets control's value.
	 * @param  array|Nette\Http\FileUpload
	 * @return self
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			$this->value = new Http\FileUpload($value);

		} elseif ($value instanceof Http\FileUpload) {
			$this->value = $value;

		} else {
			$this->value = new Http\FileUpload(NULL);
		}
		return $this;
	}


	/**
	 * Has been any file uploaded?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->value instanceof Http\FileUpload && $this->value->isOK();
	}


	/********************* validators ****************d*g**/


	/**
	 * Is file size in limit?
	 * @return bool
	 * @internal
	 */
	public static function validateFileSize(UploadControl $control, $limit)
	{
		$file = $control->getValue();
		return $file instanceof Http\FileUpload && $file->getSize() <= $limit;
	}


	/**
	 * Has file specified mime type?
	 * @return bool
	 * @internal
	 */
	public static function validateMimeType(UploadControl $control, $mimeType)
	{
		$file = $control->getValue();
		if ($file instanceof Http\FileUpload) {
			$type = strtolower($file->getContentType());
			$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
			if (in_array($type, $mimeTypes, TRUE)) {
				return TRUE;
			}
			if (in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, TRUE)) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Is file image?
	 * @return bool
	 * @internal
	 */
	public static function validateImage(UploadControl $control)
	{
		$file = $control->getValue();
		return $file instanceof Http\FileUpload && $file->isImage();
	}

}
