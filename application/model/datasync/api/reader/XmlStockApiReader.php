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
	public static function canParse(Request $request)
	{
		return self::canParseXml($request, '/request/stock', __CLASS__);
	}

	protected function findApiActionName($xml)
	{
		return parent::findApiActionNameFromXml($xml, '/request/stock');
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
					'/request/stock/set/',
					array('sku','quantity')
				);
				break;
		}
		return $request;
	}
}
?>