<?php


/**
 * Defines a common interface for different types of specification (attribute) value based filters.
 * These include filtering by numeric or date range or by predefined text values (selectors).
 *
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
interface SpecificationFilterInterface extends FilterInterface
{
	public function getSpecField();
	public function getFilterGroup();
}

?>