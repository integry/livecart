<?php

ClassLoader::import("application.model.datasync.ModelApi");
ClassLoader::import('application.model.datasync.api.reader.XmlCategoryApiReader');


/**
 * Web service access layer for User model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */
class CategoryApi extends ModelApi
{
	public static function canParse(Request $request)
	{
		if(XmlCategoryApiReader::canParse($request))
		{
			return true;
		}
		return false;
	}
	
	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$request = $this->application->getRequest();
		// ---
		$this->setParserClassName($request->get('_ApiParserClassName'));
		$cn = $this->getParserClassName();
		$this->setParser(new $cn($request->get('_ApiParserData')));
		// --
		parent::__construct('Category');
	
	}
}
?>
