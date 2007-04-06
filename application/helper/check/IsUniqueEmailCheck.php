<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if user email is unique
 *
 * @package application.helper.check
 */
class IsUniqueEmailCheck extends Check
{
	var $product;
	
	public function isValid($value)
	{
		$filter = new ARSelectFilter();
		$cond = new EqualsCond(new ARFieldHandle('User', 'email'), $value);
		$filter->setCondition($cond);
		return 0;
		return (ActiveRecordModel::getRecordCount('User', $filter) == 0);
	}
}

?>