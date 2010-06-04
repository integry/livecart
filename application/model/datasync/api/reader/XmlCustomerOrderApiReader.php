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


	public static function getXMLPath()
	{
		return '/request/order';
	}

	public function populate($updater, $profile)
	{
		parent::populate($updater, $profile, $this->xml, 
			self::getXMLPath().'/[[API_ACTION_NAME]]/[[API_FIELD_NAME]]', array('ID'));
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		$shortFormatActions = array('get','invoice', 'delete', 'capture', 'cancel'); // like <customer><delete>[customer id]</delete></customer>
		if(in_array($apiActionName, $shortFormatActions))
		{
			$request = parent::loadDataInRequest($request, '//', $shortFormatActions);
			$request->set('ID',$request->get($apiActionName));
			$request->remove($apiActionName);
		} else {
			$request = parent::loadDataInRequest($request, self::getXMLPath().'//', $this->getApiFieldNames());
		}
		return $request;
	}
	
	public function getARSelectFilter()
	{
		return parent::getARSelectFilter('CustomerOrder');
	}
}

?>
