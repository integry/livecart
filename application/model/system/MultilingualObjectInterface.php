<?php

interface MultilingualObjectInterface
{	
	public function setValueByLang($fieldName, $langCode, $value);
	public function getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty = true);
}

?>