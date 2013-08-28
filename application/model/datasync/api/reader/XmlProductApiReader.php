<?php


/**
 * Category model API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlProductApiReader extends ApiReader
{
	protected $xmlKeyToApiActionMapping = array
	(
		'list' => 'filter'
	);
	
	public static function getXMLPath()
	{
		return '/request/product';
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		$shortFormatActions = array('get','delete');
		if(in_array($apiActionName, $shortFormatActions))
		{
			$request = parent::loadDataInRequest($request, '//', $shortFormatActions);
			$request->set('SKU',$request->gget($apiActionName));
			$request->remove($apiActionName);
		} else {
			$request = parent::loadDataInRequest($request, self::getXMLPath().'//', $this->getApiFieldNames());
		}
		return $request;
	}

	public function populate($updater, $profile)
	{
		parent::populate( $updater, $profile, $this->xml,
			self::getXMLPath().'/[[API_ACTION_NAME]]/[[API_FIELD_NAME]]', array('sku'));
	}
	
	public function getARSelectFilter()
	{
		return parent::getARSelectFilter('Product');
	}

	public function sanitizeFilterField($fieldName, &$value)
	{
		if(in_array($fieldName, array('name', 'shortDescription', 'longDescription', 'pageTitle')))
		{
			$value = '%:"%'.$value.'%"%'; // lets try to find value in serialized array.
		}
		return $value;
	}
}

?>