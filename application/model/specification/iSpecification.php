<?php

/**
 * Generic interface implemented by all attribute (specification) value classes
 *
 * @package application.model.specification
 * @author Integry Systems <http://integry.com>   
 */
interface iSpecification
{
 	public function getSpecField();	  
	public function save();
	public function delete();
	public function toArray();
}

?>