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
					$request->set(ApiReader::API_PARSER_DATA ,$xml);
					$request->set(ApiReader::API_PARSER_CLASS_NAME, __CLASS__);
					return true;
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
		return parent::findApiActionNameFromXml($xml, '/request/category');
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
