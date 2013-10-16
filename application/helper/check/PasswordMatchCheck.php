<?php

namespace helper\check;

/**
 * Checks if entered passwords match
 *
 * @package application/helper/check
 * @author Integry Systems
 */

class PasswordMatchCheck extends \Phalcon\Validation\Validator implements \AngularValidation
{
	public function validate(\LiveCartValidator $validator, $field)
	{
		return $validator->request->get($this->getOption("field1"))
				== $validator->request->get($this->getOption("field2"));
	}

	public function getAngularErrType()
	{
		return 'passwordmatch';
	}

	public function getAngularValidation()
	{
		return 'password-match="' . $this->getOption("field1") . '"';
	}
}

?>