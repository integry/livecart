<?php

/**
 * Generic interface implemented by all attribute (specification) value classes
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
interface iEavSpecification
{
 	public function getField();
 	public function getFieldInstance();
	public function save();
	public function delete();
	public function toArray();
}

?>