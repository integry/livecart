<?php

namespace helper\check;

/**
 * Checks if user email is unique
 *
 * @package application/helper/check
 * @author Integry Systems
 */
class IsUniqueEmailCheck extends \Phalcon\Validation\Validator
{
	public function validate(\LiveCartValidator $validator, $field)
	{
 		$email = $validator->getValue($field);

        if (is_object(\user\User::getInstanceByEmail($email)))
        {
            $validator->appendMessage(new \Phalcon\Validation\Message($this->getOption('message'), $field));
            return false;
        }

        return true;
	}
}

?>