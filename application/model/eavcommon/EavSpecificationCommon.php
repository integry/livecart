<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.eavcommon.iEavSpecification');

/**
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
abstract class EavSpecificationCommon extends ActiveRecordModel implements iEavSpecification
{
	public function getField()
	{
		return $this->specField;
	}

	public function getFieldInstance()
	{
		return $this->getField()->get();
	}

	public function getOwner()
	{
		return $this->product;
	}

	public function set($value)
	{
		$this->value->set($value);
	}
}

?>