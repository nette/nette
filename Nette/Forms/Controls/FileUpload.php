<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2008 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @license    http://nettephp.com/license  Nette license
 * @link       http://nettephp.com
 * @category   Nette
 * @package    Nette::Forms
 * @version    $Id$
 */

/*namespace Nette::Forms;*/



require_once dirname(__FILE__) . '/../../Forms/Controls/FormControl.php';



/**
 * Text box and browse button that allow users to select a file to upload to the server.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl
 * @package    Nette::Forms
 */
class FileUpload extends FormControl
{

	/**
	 * @param  string  label
	 */
	public function __construct($label)
	{
		$this->monitor('Nette::Forms::Form');
		parent::__construct($label);
		$this->control->type = 'file';
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if ($form instanceof Form) {
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		}
	}



	/**
	 * Sets control's value.
	 * @param  array|Nette::Web::HttpUploadedFile
	 * @return void
	 */
	public function setValue($value)
	{
		if (is_array($value)) {
			$this->value = new /*Nette::Web::*/HttpUploadedFile($value);

		} elseif ($value instanceof HttpUploadedFile) {
			$this->value = $value;

		} else {
			$this->value = NULL;
		}
	}



	/**
	 * Filled validator: has been any file uploaded?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control)
	{
		$file = $control->getValue();
		return $file instanceof HttpUploadedFile && $file->isOK();
	}



	/**
	 * FileSize validator: is file size in limit?
	 * @param  IFormControl
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(IFormControl $control, $limit)
	{
		$file = $control->getValue();
		return $file instanceof HttpUploadedFile && $file->getSize() <= $limit;
	}



	/**
	 * MimeType validator: has file specified mime type?
	 * @param  IFormControl
	 * @param  string  mime type
	 * @return bool
	 */
	public static function validateMimeType(IFormControl $control, $mimeType)
	{
		$file = $control->getValue();
		return $file instanceof HttpUploadedFile && strcasecmp($file->getContentType(), $mimeType) === 0;
	}

}
