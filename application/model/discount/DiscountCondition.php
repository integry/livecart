<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.discount.DiscountAction');

class DiscountCondition extends ActiveTreeNode
{
	/**
	 * Define database schema for Category model
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		parent::defineSchema($className);

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("isAnyProduct", ARBool::instance()));
		$schema->registerField(new ARField("isValidByDate", ARBool::instance()));
		$schema->registerField(new ARField("isAllSubconditions", ARBool::instance()));

		$schema->registerField(new ARField("validFrom", ARDateTime::instance()));
		$schema->registerField(new ARField("validTo", ARDateTime::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));

		$schema->registerField(new ARField("couponCode", ARVarchar::instance(100)));
		$schema->registerField(new ARField("serializedCondition", ARText::instance()));
	}

	public static function getRootNode()
	{
		if (!$instance = self::getInstanceByIDIfExists(__CLASS__, self::ROOT_ID, false))
		{
			$instance = ActiveRecordModel::getNewInstance(__CLASS__);
			$instance->setID(self::ROOT_ID);
			$instance->lft->set(1);
			$instance->rgt->set(2);
			$instance->save();
		}

		return $instance;
	}

	public static function getNewInstance(DiscountCondition $parentCondition = null)
	{
		if (!$parentCondition)
		{
			$parentCondition = self::getRootNode();
		}

		return parent::getNewInstance(__CLASS__, $parentCondition);
	}

}

?>