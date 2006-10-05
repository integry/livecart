<?php

ClassLoader::import("library.formhandler.Form");

class LangForm extends Form {
	
	private $activeLang = null;
	private $defaultLang = null;
	private $languageList = array();
	private $multilingualFields = array();
	
	private $multilingualData = array();
	
	public function __construct($name, $requestDataArray = null, $languageList) {
		parent::__construct($name, $requestDataArray);
		
		$this->setLanguageList($languageList);
	}
	
	public function setData(&$data) {
		$dataArray = array();
		$langCode = $this->activeLang->getID();
		
		$dataArray = $data['lang'][$langCode];
		$this->multilingualData = $data['lang'];
		unset($data['lang']);
		
		$dataArray = array_merge($dataArray, $data);
		parent::setData($dataArray);
	}
	
	public function addLangField(FormField $field) {
		$field->setAttribute("lang", true, false);
		$this->multilingualFields[$field->getName()] = $field;
		parent::addField($field);
	}
	
	public function getActiveLang() {
		return $this->activeLang;
	}
	
	public function getDefaultLang() {
		return $this->defaultLang;
	}
	
	public function setLanguageList($langList) {
		$this->languageList = $langList;
	}
	
	public function getLanguageList() {
		return $this->languageList;
	}
	
	public function getMultilingualValue($langCode, $fieldName) {
		return $this->multilingualData[$langCode][$fieldName];
	}
}

?>