<?php

ClassLoader::import('application.model.filter.FilterInterface');

interface SpecificationFilterInterface extends FilterInterface
{
	public function getSpecField();
}

?>