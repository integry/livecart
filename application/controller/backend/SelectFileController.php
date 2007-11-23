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
	
	public function xmlRecursivePath()
	{
		$targetID = $this->request->get("id");
		
		if (1 == $targetID)
		{
            $dir = getcwd();
            chdir('/');
            $targetID = getcwd();
        }

        chdir($targetID);
            
		$parent = $targetID;
		$lastParent = '';
		$path = array();
		while ($parent != $lastParent)
		{
            $lastParent = $parent;

            $path[] = array('parent' => dirname($parent),
                      'ID' => $parent,
                      'name' => $parent,
                      'childrenCount' => $this->getSubDirectoryCount($parent),
                     );

            $parent = dirname($parent);
            chdir($parent);
            $parent = getcwd();            
        }
		
		$path = array_reverse($path);
		array_shift($path);
		
		$out = array();
		$o =& $out;
		
		foreach ($path as $node)
		{
            $o['children'][0] = $node;
            $o =& $o['children'][0];
        }
		
		$xmlResponse = new XMLResponse();
		
		if ($out) 
		{
			$xmlResponse->set("rootID", $out['children'][0]['parent']);
			$xmlResponse->set("tree", $out);
		}
		
		$xmlResponse->set("targetID", $targetID);
		
		return $xmlResponse;        
    }
	
	public function xmlBranch() 
	{
		$rootID = $this->request->get("id");

		$xmlResponse = new XMLResponse();
        $xmlResponse->set("rootID", $rootID);
        $xmlResponse->set("tree", $this->getSubDirectories($rootID));
		
		return $xmlResponse;
	}
		
	public function changeColumns()
	{		
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.selectFile', 'index', array('id' => $this->request->get('id')));
	}		
		
	private function getAvailableColumns()
	{
		$availableColumns = array(
                
                'fileName' => 'text',
                'fileType' => 'text',
                'fileSize' => 'numeric',
                'fileDate' => 'date',
                'filePermissions' => 'text',
                'fileOwner' => 'text',
                'fileGroup' => 'text',
        );
        
		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->translate($column), 'type' => $type);	
		}        
		
		return $availableColumns;
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
            if ($sub->isDir() && !$sub->isDot())
            {
                $node = array('parent' => $dir,
                              'ID' => $sub->getPathName(),
                              'name' => $sub->getFileName(),
                              'childrenCount' => $this->getSubDirectoryCount($sub->getPathName()),
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
    
    private function getSubDirectoryCount($dir)
    {
        foreach (new DirectoryIterator($dir) as $sub)
        {
            if ($sub->isDir() && !$sub->isDot())
            {
                return 1;
            }
        }
        
        return 0;
    }
    
    private function getFiles($dir)
    {
        
    }
}

?>