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
		$response->set('availableColumns', $this->getAvailableColumns());
		$response->set('displayedColumns', $this->getDisplayedColumns());
		$response->set('current', $dir);
				
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
                      'name' => basename($parent),
                      'childrenCount' => $this->getSubDirectoryCount($parent),
                     );

            $parent = dirname($parent);
            chdir($parent);
            $parent = getcwd();
        }

		$path = array_reverse($path);
		
		// strip root and root level directory
		array_shift($path);
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

	public function lists($dataOnly = false, $displayedColumns = null)
	{
		$filters = $this->request->get('filters');
		
		if (!$filters)
		{
			return new RawResponse();
		}
		
		$files = $this->getFiles($filters['file']);

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getDisplayedColumns();
		}

		$data = array();

		foreach ($files as $file)
		{
			$record = array();
			foreach ($displayedColumns as $column => $type)
			{
				$record[] = $file[$column];
			}

			$data[] = $record;
		}

        // searching
        if (is_array($this->request->get('filters')))
        {
            foreach ($this->request->get('filters') as $column => $filter)
            {
                if ($filter && isset($displayedColumns[$column]))
                {
                    $col = array_search($column, array_keys($displayedColumns));
                    $type = $displayedColumns[$column];
					foreach ($data as $index => $row)
                    {
                        if ('text' == $type)
                        {
							if (stripos($row[$col], $filter) === false)
	                        {
	                            unset($data[$index]);
	                        }							
						}
						else if ('numeric' == $type)
                        {	
							$filter = str_replace('<>', '!=', $filter);
							$constraints = explode(' ', $filter);
							
							foreach ($constraints as $c)
							{
								if (in_array(substr($c, 0, 2), array('!=', '<=', '>=')))
								{
									$operator = substr($c, 0, 2);
									$value = substr($c, 2);   
								}
								else if (in_array(substr($c, 0, 1), array('>', '<', '=')))
								{
									$operator = substr($c, 0, 1);
									$value = substr($c, 1);   
								}
								else
								{
									$operator = '=';
									$value = $c;
								}
								
								if ('=' == $operator)
								{
									$operator = '==';
								}
								
								if (!eval('return $row[$col]' . $operator . '$value' . ';'))
								{
									unset($data[$index]);
								}
                        	}
                    	}
                	}
            	}
            }
        }

        // sorting
        if ($this->request->isValueSet('sort_col'))
        {
            $this->sortColumn = array_search($this->request->get('sort_col'), array_keys($displayedColumns));
            usort($data, array($this, 'sortFileList'));
            
            if ('DESC' == $this->request->get('sort_dir'))
            {
                $data = array_reverse($data);
            }
        }

        // formatting
        $date = array_search('fileDate', array_keys($displayedColumns));
        foreach ($data as &$row)
        {
            if (isset($row[$date]))
            {
                $row[$date] = $this->locale->getFormattedTime($row[$date], 'date_medium');
            }
        }

		$return = array();
		$return['columns'] = array_keys($displayedColumns);
		$return['totalCount'] = count($data);
		$return['data'] = array_values($data);

		return new JSONResponse($return);
	}

    private function sortFileList($a, $b)
    {
        return strnatcasecmp($a[$this->sortColumn], $b[$this->sortColumn]);
    }

	private function getAvailableColumns()
	{
		$availableColumns = array();

		foreach ($this->getColumns() as $column => $type)
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
			$displayedColumns = $this->getColumns();
		}

		return array_intersect_key($this->getColumns(), array_flip($displayedColumns), $this->getAvailableColumns());
	}

	private function getColumns()
	{
		return array(
			'ID' => 'numeric',
			'fileName' => 'text',
			'fileType' => 'text',
			'fileSize' => 'numeric',
			'fileDate' => 'date',
			'filePermissions' => 'text',
			'fileOwner' => 'text',
			'fileGroup' => 'text',
        );
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
        try
        {
			foreach (new DirectoryIterator($dir) as $sub)
			{
				if ($sub->isDir() && !$sub->isDot())
				{
					return 1;
				}
			}
		}
		catch (RuntimeException $e)
		{
			return 0;
		}

        return 0;
    }

    private function getFiles($dir)
    {
        $ret = array();

        foreach (new DirectoryIterator($dir) as $sub)
        {
            if ($sub->isFile() && !$sub->isDot())
            {
				$node = array(
					'fileName' => $sub->getFileName(),
					'fileType' => pathinfo($sub->getFileName(), PATHINFO_EXTENSION),
					'fileSize' => round($sub->getSize() / 1024, 2),
					'fileDate' => $sub->getMTime(),
					'filePermissions' => $sub->getPerms(),
					'fileOwner' => $sub->getOwner(),
					'fileGroup' => $sub->getGroup(),
					'filePath' => $sub->getPathName(),
					'ID' => $sub->getPathName(),
				);

                $ret[$sub->getPathName()] = $node;
            }
        }

        return $ret;
    }
}

?>