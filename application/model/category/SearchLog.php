<?php

ClassLoader::import('application.model.ActiveRecordModel');

/**
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class SearchLog extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARField('keywords', ARVarchar::instance(100)));
		$schema->registerField(new ARField('ip', ARInteger::instance()));
		$schema->registerField(new ARField('time', ARDateTime::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance($query)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->keywords->set($query);
		return $instance;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->time->set(new ARSerializableDateTime());
		return parent::insert();
	}
}

?>