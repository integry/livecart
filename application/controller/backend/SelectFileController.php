<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Server side file select dialog
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class SelectFileController extends StoreManagementController
{
	public function index()
	{
        $dir = getcwd();

        chdir('/');
        		
//        		var_dump($this->getSubDirectories(getcwd()));    		exit;
        		
        $root = array('parent' => 0,
                      'ID' => getcwd(),
                      'name' => getcwd(),
                      'childrenCount' => 22,
                     );
                     
		$response = new ActionResponse();
		$response->set('directoryList', $this->getSubDirectories(getcwd()));
		$response->set('root', array(0 => $root));
		$response->set('displayedColumns', $this->getAvailableColumns());
		$response->set('availableColumns', $this->getDisplayedColumns());
		return $response;
	}
	
	protected function getAvailableColumns()
	{
		return array(
                
                'fileName' => 'text',
                'fileType' => 'text',
                'fileSize' => 'numeric',
                'fileDate' => 'date',
                'filePermissions' => 'text',
                'fileOwner' => 'text',
                'fileGroup' => 'text',

        );
	}	
	
	private function getDisplayedColumns()
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array('fileName', 'fileType', 'fileSize', 'fileDate', 'filePermissions', 'fileOwner', 'fileGroup');
		}
		
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		return $displayedColumns;		
	}	
	
	private function getSubDirectories($dir)
	{
        $ret = array();
        
        foreach (new DirectoryIterator($dir) as $sub)
        {
            if ($sub->isDir())
            {
                $node = array('parent' => $dir,
                              'ID' => $sub->getPathName(),
                              'name' => $sub->getFileName(),
                              'childrenCount' => 22,
                             );
                             
                $ret[$sub->getPathName()] = $node;
            }
        }
        
        uksort($ret, 'strnatcasecmp');
        
        // set numeric indexes
        $out = array();
        foreach ($ret as $node)
        {
            $out[] = $node;
        }
        
        return $out;
    }
    
    private function getFiles($dir)
    {
        
    }
}

?>
