<?php


/**
 * Category model API XML format request parsing (reading/routing)
 *
 * @package application/model/datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlCategoryApiReader extends ApiReader
{
	protected $xmlKeyToApiActionMapping = array
	(
		'list' => 'filter'
	);
	
	public static function getXMLPath()
	{
		return '/request/category';
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
			$request = parent::loadDataInRequest($request, self::getXMLPath().'//', $this->getApiFieldNames());
		}
		return $request;
	}
}

?>