<?php

namespace tax;

/**
 * Defines a tax class
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class TaxClass extends \system\MultilingualObject
{
	public $ID;
	public $name;
	public $position;

	/**
	 * Load record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$filter->orderBy(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Get a list of all existing classes
	 *
	 * @param boolean $loadReferencedRecords Load referenced records
	 *
	 * @return ARSet
	 */
	public static function getAllClasses($loadReferencedRecords = false)
	{
		$f = select();
		$f->orderBy(f('TaxClass.position'));

		return self::getRecordSet($f, $loadReferencedRecords);
	}

	public static function findByName($name)
	{
		$f = select(
				new EqualsCond(
					MultiLingualObject::getLangSearchHandle(
						'TaxClass.name',
						self::getApplication()->getDefaultLanguageCode()
					),
					$name
				)
			);

		return ActiveRecordModel::getRecordSet('TaxClass', $f)->shift();
	}

	public function beforeCreate()
	{
		$this->setLastPosition();
	}
}

?>
