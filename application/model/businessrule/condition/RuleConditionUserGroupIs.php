<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionUserGroupIs extends RuleCondition
{
	public function isApplicable()
	{
		$user = $this->getContext()->getUser();

		if (!$user)
		{
			return null;
		}

		if (!$user->isLoaded())
		{
			$user->load();
		}

		$userGroup = $user->userGroup;
		$userGroupID = $userGroup ? $userGroup->getID() : null;

		if (!$userGroupID)
		{
			return null;
		}

		foreach ($this->records as $record)
		{
			if ($record['ID'] == $userGroupID)
			{
				return true;
			}
		}
	}

	public static function getSortorderBy()
	{
		return 4;
	}
}

?>