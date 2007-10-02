<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if user email is unique
 *
 * @package application.helper.check
 * @author Integry Systems 
 */
class IsUniqueEmailCheck extends Check
{
	public function isValid($value)
	{
		ClassLoader::import('application.model.user.User');
		
		$filter = new ARSelectFilter();
		$cond = new EqualsCond(new ARFieldHandle('User', 'email'), $value);
		$filter->setCondition($cond);
		return (ActiveRecordModel::getRecordCount('User', $filter) == 0);
	}
}

?>