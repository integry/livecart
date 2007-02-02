<?php

interface iSpecification
{
 	//public function setValue($value);
 	public function getSpecField();	  
	public function save();
	public function delete();
	public function toArray();
}

?>