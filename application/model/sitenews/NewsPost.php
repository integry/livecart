<?php


/**
 * News post entry
 *
 * @package application/model/news
 * @author Integry Systems <http://integry.com>
 */
class NewsPost extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__CLASS__);

		public $ID;
		public $isEnabled;
		public $position;
		public $time;
		public $title;
		public $text;
		public $moreText;
	}

	protected function insert()
	{
	  	$this->setLastPosition();
		return parent::insert();
	}
}

?>