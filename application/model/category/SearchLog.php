<?php


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

		public $ID;
		public $keywords', ARVarchar::instance()));
		public $ip;
		public $time', ARDateTime::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance($query)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->keywords = $query);
		return $instance;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->time = new ARSerializableDateTime());
		return parent::insert();
	}
}

?>