<?php

namespace eav;

use \eavcommon\EavValueCommon;
use Phalcon\Mvc\Model\Validator;

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
	public $ID;
	public $fieldID;
	public $position;
	public $value;

	public function initialize()
	{
		$this->belongsTo('fieldID', 'eav\EavField', 'ID', array('alias' => 'EavField'));
	}

    public function validation()
    {
        $this->validate(new Validator\PresenceOf(
            array(
                "field"  => "value"
            )
        ));

        return $this->validationHasFailed() != true;
    }

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
		if (!in_array($field->type, array(EavField::TYPE_NUMBERS_SELECTOR, EavField::TYPE_TEXT_SELECTOR)))
		{
			throw new Exception('Cannot create an EavValue for non-selector field!');
		}

		$instance = new EavValue;
		$instance->fieldID = $field->getID();

		return $instance;
	}
	
	public static function restoreInstance(EavField $field, $valueId, $value)
	{
		$instance = self::getNewInstance($field);
		$instance->setID($valueId);
		$instance->value = unserialize($value);

		return $instance;
	}
}
?>
