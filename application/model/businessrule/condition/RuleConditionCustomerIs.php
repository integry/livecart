<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionCustomerIs extends RuleCondition
{
	public function isApplicable()
	{
		$user = $this->getContext()->getUser();
		$userGroup = $user->userGroup->get();
		$userID = $user->getID();
		$userGroupID = $userGroup ? $userGroup->getID() : null;

		foreach ($this->records as $record)
		{
			if (($record['userID'] == $userID) || ($record['userGroupID'] == $userGroupID))
			{
				return true;
			}
		}
	}
}

?>