<?php


/**
 * EavFieldGroupCommon allow to group related EavFieldCommon (fields) together.
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
class EavFieldGroupCommon extends MultilingualObject
{
	/**
	 * Define SpecFieldGroup database schema
	 */
	public static function defineSchema($className)
	{
				$schema->setName($className);

		public $ID;
		public $name;
		public $position;

		return $schema;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		// get max position
	  	$f = new ARSelectFilter();
	  	$f->setCondition($this->getParentCondition());
	  	$f->setOrder(new ARFieldHandle(get_class($this), 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray(get_class($this), $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		$this->position = $position;

		return parent::insert();
	}
}

?>