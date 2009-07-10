<?php

ClassLoader::import('application.model.businessrule.RuleCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionUserGroupIs extends RuleCondition
{
	public function isApplicable()
	{
		$user = $this->getContext()->getUser();
		$userGroup = $user->userGroup->get();
		$userGroupID = $userGroup ? $userGroup->getID() : null;

		foreach ($this->records as $record)
		{
			if ($record['userGroupID'] == $userGroupID)
			{
				return true;
			}
		}
	}

	public static function getSortOrder()
	{
		return 4;
	}
}

?>