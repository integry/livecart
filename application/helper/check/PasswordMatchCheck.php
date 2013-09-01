<?php

namespace helper\check;

/**
 * Checks if entered passwords match
 *
 * @package application/helper/check
 * @author Integry Systems
 */

class PasswordMatchCheck extends \Phalcon\Validation\Validator
{
	public function validate(\LiveCartValidator $validator, $field)
	{
		return $validator->request->get($this->getOption("fieldName"))
				== $validator->request->get($this->getOption("confFieldName"));
	}
}

?>