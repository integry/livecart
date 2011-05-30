<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.eav.EavAble');
ClassLoader::import('application.model.eav.EavObject');
ClassLoader::import('application.model.product.ManufacturerImage');

/**
 * Defines a product manufacturer. Each product can be assigned to one manufacturer.
 * Keeping manufacturers as a separate entity allows to manipulate them more easily and
 * provide more effective product filtering (search by manufacturers).
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class Manufacturer extends ActiveRecordModel implements EavAble
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(60)));
		$schema->registerField(new ARForeignKeyField("defaultImageID", "ManufacturerImage", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
		$schema->registerAutoReference('defaultImageID');
	}

	public static function getNewInstance($name)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->name->set($name);
		return $instance;
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData = false, $loadReferencedRecords = false);
	}

	public static function getInstanceByName($name)
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('Manufacturer', 'name'), $name));
		$filter->setLimit(1);
		$set = ActiveRecordModel::getRecordSet('Manufacturer', $filter);
		if ($set->size() > 0)
		{
			return $set->get(0);
		}
		else
		{
			return self::getNewInstance($name);
		}
	}

	/**
	 *
	 * @return array('manufacturers'=> array of manufacturers, 'counts'=> manufacturer product count, 'count'=> count of manufacturers)
	 */
	public static function getActiveProductManufacturers($context)
	{
		$context = $context + array('startingWith' => null, 'currentPage'=>1);
		extract($context); // creates $startingWith and $currentPage

		$config = ActiveRecordModel::getApplication()->getConfig();
		$listStyle = $config->get('MANUFACTURER_PAGE_LIST_STYLE');
		$perPage = $config->get('MANUFACTURER_PAGE_PER_PAGE');

		// get filter to select manufacturers of active products only
		$f = new ARSelectFilter();

		$ids = $counts = $letters = array();
		$sql = !self::getApplication()->getConfig()->get('MANUFACTURER_PAGE_DISPLAY_ACTIVE') ? 'SELECT DISTINCT(ID) as manufacturerID, 1 AS cnt FROM Manufacturer ' . $f->createString() . ' GROUP BY manufacturerID' : 'SELECT DISTINCT(manufacturerID), COUNT(*) AS cnt FROM Product ' . $f->createString() . ' GROUP BY manufacturerID';
		foreach (ActiveRecordModel::getDataBySQL($sql) as $row)
		{
			$ids[] = $row['manufacturerID'];
			$counts[$row['manufacturerID']] = $row['cnt'];
		}

		$f = new ARSelectFilter(new InCond(new ARFieldHandle('Manufacturer', 'ID'), $ids));
		$f->addField('UPPER(LEFT(TRIM(Manufacturer.name),1))', '', 'FirstLetter');
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Manufacturer', 'name'), ''));

		if ($startingWith)
		{
			$f->mergeCondition(new LikeCond(new ARFieldHandle('Manufacturer', 'name'), $startingWith.'%'));
		}
		$f->setOrder(new ARFieldHandle('Manufacturer', 'name'));

		if ($perPage > 0)
		{
			$offsetStart = (($currentPage - 1) * $perPage) + 1;
			$offsetEnd = $currentPage * $perPage;
			$f->setLimit($perPage, $offsetStart - 1);
		}
		$manufacturers = ActiveRecordModel::getRecordSetArray(__CLASS__, $f);
		foreach($manufacturers as $item)
		{
			$letters[$item['FirstLetter']] = $item['FirstLetter'];
		}
		return array('manufacturers' => $manufacturers,'counts' => $counts, 'count' => ActiveRecordModel::getRecordCount(__CLASS__, $f));
	}

	public static function getActiveProductManufacturerFirstLetters()
	{
		$letters = $ids = array();
		$f = new ARSelectFilter();
		$sql = !self::getApplication()->getConfig()->get('MANUFACTURER_PAGE_DISPLAY_ACTIVE') ? 'SELECT DISTINCT(ID) as manufacturerID FROM Manufacturer ' . $f->createString() . ' GROUP BY manufacturerID' : 'SELECT DISTINCT(manufacturerID) FROM Product ' . $f->createString() . ' GROUP BY manufacturerID';
		foreach (ActiveRecordModel::getDataBySQL($sql) as $row)
		{
			$ids[] = $row['manufacturerID'];
		}

		$f = new ARSelectFilter(new InCond(new ARFieldHandle('Manufacturer', 'ID'), $ids));
		$f->addField('UPPER(LEFT(TRIM(Manufacturer.name),1))', '', 'FirstLetter');
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Manufacturer', 'name'), ''));
		$f->setOrder(new ARFieldHandle('Manufacturer', 'name'));

		$manufacturers = ActiveRecordModel::getRecordSetArray('Manufacturer', $f);
		foreach($manufacturers as $item)
		{
			$letters[$item['FirstLetter']] = $item['FirstLetter'];
		}
		return $letters;
	}
}

?>