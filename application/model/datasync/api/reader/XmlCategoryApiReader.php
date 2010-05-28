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
	protected $xmlKeyToApiActionMapping = array
	(
		'list' => 'filter'
	);

	public static function canParse(Request $request)
	{
		return self::canParseXml($request, '/request/category', __CLASS__);
	}

	protected function findApiActionName($xml)
	{
		return parent::findApiActionNameFromXml($xml, '/request/category');
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		$shortFormatActions = array('get','delete');
		if(in_array($apiActionName, $shortFormatActions))
		{
			$request = parent::loadDataInRequest($request, '//', $shortFormatActions);
			$request->set('ID',$request->get($apiActionName));
			$request->remove($apiActionName);
		} else {
			$request = parent::loadDataInRequest($request, '/request/category//', $this->getApiFieldNames());
		}
		return $request;
	}
}

?>