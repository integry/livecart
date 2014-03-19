<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application/helper/massAction
 * @author Integry Systems
 */
class UserMassActionProcessor extends MassActionProcessor
{
	protected function processRecord(User $user)
	{
		if (substr($this->getAction(), 0, 7) == 'enable_')
		{
			$user->writeAttribute('isEnabled', 1);
		}
		else if (substr($this->getAction(), 0, 8) == 'disable_')
		{
			$user->writeAttribute('isEnabled', 0);
		}
	}
}

?>