<?php

namespace eav;

use \eavcommon\EavValueCommon;

/**
 * Attribute selector value. The same selector value can be assigned to multiple products and usually
 * can be selected from a list of already created values when entering product information - as opposed to
 * input values (numeric or string) that are related to one product only. The advantage of selector values
 * is that they can be used to create product Filters, while input string (SpecificationStringValues) can not.
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
class EavValue extends EavValueCommon
{
//	public $fieldID", "EavField", "ID", "EavField;

	public $ID;
	public $position;
	public $value;

	protected function getFieldClass()
	{
		return 'EavField';
	}

	/*####################  Static method implementations ####################*/

	/**
	 *  Get new instance of specification field value
	 *
	 *	@param SpecField $field Instance of SpecField (must be a selector field)
	 *  @return SpecFieldValue
	 */
	public static function getNewInstance(EavField $field)
	{
		if (!in_array($field->type, array(EavFieldCommon::TYPE_NUMBERS_SELECTOR, EavFieldCommon::TYPE_TEXT_SELECTOR)))
		{
			throw new Exception('Cannot create an EavValue for non-selector field!');
		}

		$instance = new EavValue;
		$instance->fieldID = $field->getID();

		return $instance;
	}
	
	public static function restoreInstance(EavFieldCommon $field, $valueId, $value)
	{
		$instance = self::getNewInstance($field);
		$instance->setID($valueId);
		$instance->value = unserialize($value);

		return $instance;
	}
}
?>
