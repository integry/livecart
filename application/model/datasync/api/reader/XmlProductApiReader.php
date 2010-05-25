<?php

ClassLoader::import('application.model.datasync.api.reader.ApiReader');

/**
 * Category model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlProductApiReader extends ApiReader
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
				if(count($xml->xpath('/request/product')) == 1)
				{
					$request->set(ApiReader::API_PARSER_DATA ,$xml);
					$request->set(ApiReader::API_PARSER_CLASS_NAME, __CLASS__);
					return true;
				}
			}
		}
		return false;
	}

	public function __construct($xml, $fieldNames)
	{
		$this->xml = $xml; // $this->setDataResource(); // or smth.
		
		$this->setApiFieldNames($fieldNames);
		$this->findApiActionName($xml);
	}
	
	protected function findApiActionName($xml)
	{
		return parent::findApiActionNameFromXml($xml, '/request/product');
	}

	public function loadDataInRequest($request)
	{
		if($this->getApiActionName() == 'get')
		{
			$request = parent::loadDataInRequest($request, '//', array('get'));
			$request->set('SKU',$request->get('get'));
			$request->remove('get');
		} else {
			$request = parent::loadDataInRequest($request, '/request/product//', $this->getApiFieldNames());
		}
		return $request;
	}

	public function populate($updater, $profile)
	{
		parent::populate( $updater, $profile, $this->xml,
			'/request/product/[[API_ACTION_NAME]]/[[API_FIELD_NAME]]', array('sku'));
	}
}
