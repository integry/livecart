<?php

ClassLoader::import('application.model.datasync.api.reader.ApiReader');

/**
 * Category model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlCategoryApiReader extends ApiReader
{
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
		$apiActionName = array_key_exists($apiActionName,$this->xmlKeyToApiActionMapping)?$this->xmlKeyToApiActionMapping[$apiActionName]:$apiActionName;
		$this->setApiActionName($apiActionName);

		return $this->getApiActionName(); // just to test that it is set!
	}

	public function loadDataInRequest($request)
	{
		if($this->getApiActionName() == 'get')
		{
			$request = parent::loadDataInRequest($request, '//', array('get'));
			// for get request <category><get>[ID]</get></category> rename content in node <get> to ID
			$request->set('ID',$request->get('get'));
			$request->remove('get');
		} else {
			$request = parent::loadDataInRequest($request, '/request/category//', $this->getApiFieldNames());
		}
		return $request;
	}
}
