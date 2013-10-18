<?php

namespace eavcommon;

/**
 * Specification attributes allow to define specific product models with a specific set of features or parameters.
 *
 * Each SpecField is a separate attribute. For example, screen size for laptops, ISBN code for books,
 * horsepowers for cars, etc. Since SpecFields are linked to categories, products from different categories can
 * have different set of attributes.
 *
 * @package application/model/eavcommon
 * @author Integry Systems <http://integry.com>
 */
abstract class EavFieldCommon extends \system\MultilingualObject
{
	/**
	 * Referenced class name (for example, Product)
	 */
	public abstract function getOwnerClass();

	public abstract function getStringValueClass();

	public abstract function getNumericValueClass();

	public abstract function getDateValueClass();

	public abstract function getSelectValueClass();

	public abstract function getMultiSelectValueClass();

	public abstract function getFieldIDColumnName();

	public abstract function getObjectIDColumnName();

	public abstract function getOwnerIDColumnName();

	protected abstract function getParentCondition();


}

?>
