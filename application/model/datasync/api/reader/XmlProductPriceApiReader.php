<?php

ClassLoader::import('application.model.datasync.api.reader.ApiReader');

/**
 * ProductPrice API XML format request parsing (reading/routing)
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlProductPriceApiReader extends ApiReader
{
	public static function canParse(Request $request)
	{
		return self::canParseXml($request, '/request/price', __CLASS__);
	}

	protected function findApiActionName($xml)
	{
		return parent::findApiActionNameFromXml($xml, '/request/price');
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		$shortFormatActions = array('get');
		if(in_array($apiActionName, $shortFormatActions))
		{
			$request = parent::loadDataInRequest($request, '//', $shortFormatActions);
			$request->set('SKU',$request->get($apiActionName));
			$request->remove($apiActionName);
		} else {
			$request = parent::loadDataInRequest($request, '/request/customer//', $this->getApiFieldNames());
		}
		return $request;
	}
}
?>