<?php


/**
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
abstract class EavSpecificationCommon extends ActiveRecordModel implements iEavSpecification
{
	abstract public function getValue();

	public function getField()
	{
		return $this->specField;
	}

	public function getFieldInstance()
	{
		return $this->getField();
	}

	public function getOwnerVarName()
	{
		return 'product';
	}

	public function getOwner()
	{
		$field = $this->getOwnerVarName();
		return $this->$field;
	}

	public function setOwner(ActiveRecordModel $owner)
	{
		$field = $this->getOwnerVarName();
		$this->$field->set($owner);
	}

	public function set($value)
	{
		$this->value->set($value);
	}
}

?>