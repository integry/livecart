<?php

ClassLoader::import('application.model.datasync.api.reader.ApiReader');

/**
 * CustomerOrder model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlCustomerOrderApiReader extends ApiReader
{
	const HANDLE = 0;
	const CONDITION = 1;
	const ALL_KEYS = -1;

	protected $xmlKeyToApiActionMapping = array(
		'list' => 'filter'
	);
	
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
				if(count($xml->xpath('/request/order')) == 1)
				{
					$request->set(ApiReader::API_PARSER_DATA ,$xml);
					$request->set(ApiReader::API_PARSER_CLASS_NAME, __CLASS__);
					return true;
				}
			}
		}
		return false;
	}

	public function populate($updater, $profile)
	{
		parent::populate($updater, $profile, $this->xml, 
			'/request/order/[[API_ACTION_NAME]]/[[API_FIELD_NAME]]', array('ID'));
	}
	
	public function sanitizeFilterField($name, &$value)
	{
		return $value;
	}

	protected function findApiActionName($xml)
	{
		return parent::findApiActionNameFromXml($xml, '/request/order');
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		$shortFormatActions = array('get','delete'); // like <customer><delete>[customer id]</delete></customer>
		if(in_array($apiActionName, $shortFormatActions))
		{
			$request = parent::loadDataInRequest($request, '//', $shortFormatActions);
			$request->set('ID',$request->get($apiActionName));
			$request->remove($apiActionName);
		} else {
			$request = parent::loadDataInRequest($request, '/request/order//', $this->getApiFieldNames());
		}
		return $request;
	}
}
