<?php


/**
 * EavFieldGroups allow to group related attributes (EavFields) together.
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
class EavFieldGroup extends EavFieldGroupCommon
{
	public static function defineSchema()
	{
		$schema = parent::defineSchema(__CLASS__);
		$schema->registerField(new ARField("classID", ARInteger::instance()));
		$schema->registerField(new ARField("stringIdentifier", ARVarchar::instance(40)));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get new EavFieldGroup active record instance
	 *
	 * @return EavFieldGroup
	 */
	public static function getNewInstance($className)
	{
		if ($className instanceof EavFieldManager)
		{
			$className = $className->getClassName();
		}

		$inst = new self();
		$inst->classID->set(EavField::getClassID($className));

		return $inst;
	}

	public function toArray()
	{
		$array = parent::toArray();
		$array['Category']['ID'] = $array['classID'];
		return $array;
	}

	protected function getParentCondition()
	{
		return new EqualsCond('classID', $this->classID);
	}
}

?>
