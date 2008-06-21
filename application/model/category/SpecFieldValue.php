<?php

ClassLoader::import('application.model.eav.EavValueCommon');
ClassLoader::import('application.model.specification.SpecificationItem');

/**
 * Attribute selector value. The same selector value can be assigned to multiple products and usually
 * can be selected from a list of already created values when entering product information - as opposed to
 * input values (numeric or string) that are related to one product only. The advantage of selector values
 * is that they can be used to create product Filters, while input string (SpecificationStringValues) can not.
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class SpecFieldValue extends EavValueCommon
{
	/**
	 * Define SpecFieldValue schema in database
	 */
	public static function defineSchema()
	{
		$schema = parent::defineSchema(__CLASS__);
		$schema->registerField(new ARForeignKeyField("specFieldID", "SpecField", "ID", "SpecField", ARInteger::instance()));
	}

	protected function getFieldClass()
	{
		return 'SpecField';
	}

	/*####################  Static method implementations ####################*/

	/**
	 *  Get new instance of specification field value
	 *
	 *	@param SpecField $field Instance of SpecField (must be a selector field)
	 *  @return SpecFieldValue
	 */
	public static function getNewInstance(SpecField $field)
	{
		return parent::getNewInstance(__CLASS__, $field);
	}

	/**
	 * Get active record instance
	 *
	 * @param integer $recordID
	 * @param boolean $loadRecordData
	 * @param boolean $loadReferencedRecords
	 * @return SpecFieldValue
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Loads a record set of specification field values belonging to specification field
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSet($specFieldId)
	{
		return parent::getRecordSet(__CLASS__, $specFieldId);
	}

	/**
	 * Loads a record set of specification field values belonging to specification field and returns it as array
	 *
	 * @param integer $specFieldId
	 * @return ARSet
	 */
	public static function getRecordSetArray($specFieldId)
	{
		return parent::getRecordSetArray(__CLASS__, $specFieldId);
	}

	public static function restoreInstance(SpecField $field, $valueId, $value)
	{
		return parent::restoreInstance(__CLASS__, $field, $valueId, $value);
	}
}
?>