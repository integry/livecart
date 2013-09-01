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
	public function validate()
	{
		return $this->request->get($this->getParam("fieldName"))
				== $this->request->get($this->getParam("confFieldName"));
	}
}

?>