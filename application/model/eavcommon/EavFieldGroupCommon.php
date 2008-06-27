<?php

ClassLoader::import("application.model.system.ActiveRecordGroup");

/**
 * EavFieldGroupCommon allow to group related EavFieldCommon (fields) together.
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
class EavFieldGroupCommon extends MultilingualObject
{
	/**
	 * Define SpecFieldGroup database schema
	 */
	public static function defineSchema($className)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));

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

		$this->position->set($position);

		return parent::insert();
	}
}

?>