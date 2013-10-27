<?php

/**
 * Pre-defined address state/province. When there are states defined for a country the system
 * will always offer to select the address from a drop-down list. Otherwise the user has to
 * enter the address manually. It would be a good idea to pre-define states for the countries
 * your store serves (the ones that bring the most orders).
 *
 * @package application/model/delivery
 * @author Integry Systems <http://integry.com>
 */
class State extends ActiveRecordModel
{
	/**
	 * Define database schema
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $countryID;
		public $code;
		public $name;
		public $subdivisionType;
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ActiveRecord
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/*####################  Instance retrieval ####################*/

	public static function getStatesByCountry($countryCode)
	{
		$f = new ARSelectFilter();
		$f->setCondition('State.countryID = :State.countryID:', array('State.countryID' => $countryCode));
		$f->orderBy('State.name');
		$stateArray = ActiveRecordModel::getRecordSetArray('State', $f);

		$states = array();
		foreach ($stateArray as $state)
		{
			$states[$state['ID']] = $state['name'];
		}

		return $states;
	}

	public static function getAllStates()
	{
		return ActiveRecordModel::getRecordSet(__CLASS__, new ARSelectFilter());
	}

	public static function getAllStatesArray()
	{
		return ActiveRecordModel::getRecordSetArray(__CLASS__, new ARSelectFilter());
	}

	public static function getStateIDByName($countryCode, $name)
	{
		$f = new ARSelectFilter();
		$f->setCondition('State.countryID = :State.countryID:', array('State.countryID' => $countryCode));

		$nameCond = 'State.name = :State.name:', array('State.name' => $name);
		$nameCond->addOr('State.code = :State.code:', array('State.code' => $name));
		$f->andWhere($nameCond);

		$f->orderBy('State.name');
		$f->limit(1);
		$stateArray = ActiveRecordModel::getRecordSetArray('State', $f);

		if ($stateArray)
		{
			return $stateArray[0]['ID'];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Provides an additional verification that state belongs to the particular country
	 *
	 * @return ActiveRecord
	 */
	public static function getStateByIDAndCountry($stateID, $countryID)
	{
		$f = new ARSelectFilter();
		$f->setCondition('State.ID = :State.ID:', array('State.ID' => $stateID));
		$f->andWhere('State.countryID = :State.countryID:', array('State.countryID' => $countryID));

		$f->limit(1);
		$states = ActiveRecordModel::getRecordSet('State', $f);

		if ($states)
		{
			return $states->get(0);
		}
		else
		{
			return null;
		}
	}
}

?>