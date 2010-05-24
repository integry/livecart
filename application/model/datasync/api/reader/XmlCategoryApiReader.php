<?php

ClassLoader::import('application.model.datasync.api.reader.CategoryApiReader');

/**
 * Category model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlCategoryApiReader extends CategoryApiReader
{
	private $apiActionName;
	private $listFilterMapping;
	

	public static function canParse(Request $request)
	{
		$get = $request->getRawGet();
		if(array_key_exists('xml',$get))
		{
			$xml = self::getSanitizedSimpleXml($get['xml']);
			if($xml != null)
			{
				if(count($xml->xpath('/request/category')) == 1)
				{
					$request->set('_ApiParserData',$xml);
					$request->set('_ApiParserClassName', 'XmlCategoryApiReader');
					return true; // yes, can parse
				}
			}
		}
	}

	public function __construct($xml, $fieldNames)
	{
		$this->xml = $xml; // $this->setDataResource(); // or smth.
		
		$this->setApiFieldNames($fieldNames);
		$this->findApiActionName($xml);
	}
	
	public function getApiActionName()
	{
		return $this->apiActionName;
	}

	protected function findApiActionName($xml)
	{
		$apiActionName = null; // not known
		foreach($xml->xpath('/request/category') as $k=>$v) // iterate over category elements
		{
			foreach($v as $k2 => $v2) // with each category element
			{
				$apiActionName = $k2; // first element name is action name!
				break 2;
			}
		}
		$this->apiActionName = array_key_exists($apiActionName,$this->xmlKeyToApiActionMapping)?$this->xmlKeyToApiActionMapping[$apiActionName]:$apiActionName;
		return $apiActionName;
	}

	public function loadDataInRequest($request)
	{
		return parent::loadDataInRequest($request, '/request/category//', $this->fieldNames);
	}
}
