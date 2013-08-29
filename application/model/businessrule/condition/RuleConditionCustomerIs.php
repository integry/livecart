<?php

ClassLoader::import('application/model/businessrule/RuleCondition');
ClassLoader::import('application/model/businessrule/interface/RuleOrderCondition');

/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionCustomerIs extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		$user = $this->getContext()->getUser();

		if (!$user)
		{
			return;
		}

		$userGroup = $user->userGroup->get();
		$userID = $user->getID();
		$userGroupID = $userGroup ? $userGroup->getID() : null;

		foreach ($this->records as $record)
		{
			if ((!empty($record['userID']) && ($record['userID'] == $userID)) || (!empty($record['userGroupID']) && ($record['userGroupID'] == $userGroupID)))
			{
				return true;
			}
		}
	}

	public static function getSortOrder()
	{
		return 5;
	}
}

?>