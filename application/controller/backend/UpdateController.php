<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');

/**
 *  @role update
 */
class UpdateController extends StoreManagementController
{
	public function index()
	{
		// get the newest version
        $f = fsockopen('update.livecart.com', '80', $err);
        if ($err)
        {
            return new ActionResponse('err', true);
        }

        $out = "GET /version HTTP/1.1\r\n";
        $out .= "Host: update.livecart.com\r\n";
        $out .= "Connection: Close\r\n\r\n";
    
        fwrite($f, $out);
        $res = '';
        while (!feof($f)) 
        {
            $res .= fgets($f, 128);
        }
        
        $res = str_replace("\r", '', $res);
        list($headers, $version) = explode("\n\n", $res);
        
        // get current version
        $current = file_get_contents(ClassLoader::getRealPath('.') . '/.version');
        
        $response = new ActionResponse('current', $current);
        $response->set('newest', $version);
        $response->set('needUpdate', version_compare($current, $version, '<'));
        return $response;
	}	
}

?>