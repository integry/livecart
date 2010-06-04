<?php

ClassLoader::import('application.model.datasync.api.reader.ApiReader');

/**
 * Stock API XML format request parsing
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 */

class XmlStockApiReader extends ApiReader
{
	public static function getXMLPath()
	{
		return '/request/stock';
	}

	public function loadDataInRequest($request)
	{
		$apiActionName = $this->getApiActionName();
		switch($apiActionName)
		{
			case 'get':
				$request = parent::loadDataInRequest($request, '//', array($apiActionName));
				// rename get to SKU
				$request->set('SKU',$request->get($apiActionName));
				$request->remove($apiActionName);			
				break;

			case 'set':
				// 'flat' fields
				$request = parent::loadDataInRequest($request,
					self::getXMLPath().'/'.$apiActionName.'/',
					array('sku','quantity')
				);
				break;
		}
		return $request;
	}
}
?>