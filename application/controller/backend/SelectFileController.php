<?php


/**
 * Server side file select dialog
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role serverfile
 */
class SelectFileController extends ActiveGridController
{
	protected function getClassName()
	{
		return null;
	}

	public function indexAction()
	{
		$dir = getcwd();

		@chdir('/');

		$root = array('parent' => 0,
					  'ID' => getcwd(),
					  'name' => getcwd(),
					  'childrenCount' => 22,
					 );

		$response = new ActionResponse();
		$response->set('directoryList', $this->getSubDirectories(getcwd()));
		$response->set('root', array(0 => $root));
		$response->set('current', $dir);

		$this->setGridResponse($response);
		$response->set('offset', 0);
		$response->set('data', '');

		//print_r($response->getData());

		return $response;
	}

	public function xmlRecursivePathAction()
	{
		$targetID = $this->request->gget("id");

		if (1 == $targetID)
		{
			$dir = getcwd();
			@chdir('/');
			$targetID = getcwd();
		}

		@chdir($targetID);

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
			@chdir($parent);
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
			$o['children'] = $this->getSubDirectories($node['parent']);

			foreach ($o['children'] as $i => $child)
			{
				if ($child['ID'] == $node['ID'])
				{
					break;
				}
			}

			$o =& $o['children'][$i];
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

	public function xmlBranchAction()
	{
		$rootID = $this->request->gget("id");

		$xmlResponse = new XMLResponse();
		$xmlResponse->set("rootID", $rootID);
		$xmlResponse->set("tree", $this->getSubDirectories($rootID));

		return $xmlResponse;
	}

	public function changeColumnsAction()
	{
		$columns = array_keys($this->request->gget('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.selectFile', 'index', array('id' => $this->request->gget('id')));
	}

	public function listsAction($dataOnly = false, $displayedColumns = null)
	{
		$filters = $this->request->gget('filters');

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
		if (is_array($this->request->gget('filters')))
		{
			foreach ($this->request->gget('filters') as $column => $filter)
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
		if (!$this->request->isValueSet('sort_col'))
		{
			$this->request->set('sort_col', 'name');
		}

		$this->sortColumn = array_search($this->request->gget('sort_col'), array_keys($displayedColumns));
		usort($data, array($this, 'sortFileList'));

		if ('DESC' == $this->request->gget('sort_dir'))
		{
			$data = array_reverse($data);
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

	public function getAvailableColumnsAction()
	{
		$availableColumns = array();

		foreach ($this->getColumns() as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->translate($column), 'type' => $type);
		}

		return $availableColumns;
	}

	protected function getDisplayedColumns()
	{
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');

		if (!$displayedColumns)
		{
			$displayedColumns = array_keys($this->getColumns());
		}

		return array_intersect_key($this->getColumns(), array_flip($displayedColumns), $this->getAvailableColumns());
	}

	protected function getDefaultColumns()
	{
		return array();
	}

	protected function getColumns()
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

		try
		{
			$dir = urldecode($dir);
			$iterator = new DirectoryIterator($dir);
		}
		catch (RuntimeException $e)
		{
			return $ret;
		}

		foreach ($iterator as $sub)
		{
			if ($sub->isFile() && !$sub->isDot())
			{
				$node = array(
					'fileName' => $sub->getFileName(),
					'fileType' => pathinfo($sub->getFileName(), PATHINFO_EXTENSION),
					'fileSize' => round($sub->getSize() / 1024, 2),
					'fileDate' => $sub->getMTime(),
					'filePermissions' => $this->getFilePerms($sub->getPathName()),
					'fileOwner' => $sub->getOwner(),
					'fileGroup' => $sub->getGroup(),
					'filePath' => $sub->getPathName(),
					'ID' => $sub->getPathName(),
				);

				if (function_exists('posix_getpwuid') && ($user = posix_getpwuid($node['fileOwner'])))
				{
					$node['fileOwner'] = $user['name'];
				}

				if (function_exists('posix_getpwuid') && ($group = posix_getgrgid($node['fileGroup'])))
				{
					$node['fileGroup'] = $group['name'];
				}

				$ret[$sub->getPathName()] = $node;
			}
		}

		return $ret;
	}

	function getFilePerms($file)
	{
		$perms = fileperms($file);
		if (($perms & 0xC000) == 0xC000) {$info = 's'; } // Socket
		elseif (($perms & 0xA000) == 0xA000) {$info = 'l'; } // Symbolic Link
		elseif (($perms & 0x8000) == 0x8000) {$info = '-'; } // Regular
		elseif (($perms & 0x6000) == 0x6000) {$info = 'b'; } // Block special
		elseif (($perms & 0x4000) == 0x4000) {$info = 'd'; } // Directory
		elseif (($perms & 0x2000) == 0x2000) {$info = 'c'; } // Character special
		elseif (($perms & 0x1000) == 0x1000) {$info = 'p'; } // FIFO pipe
		else {$info = '?';} // Unknown
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
		   (($perms & 0x0800) ? 's' : 'x' ) :
		   (($perms & 0x0800) ? 'S' : '-'));
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
			  (($perms & 0x0400) ? 's' : 'x' ) :
			  (($perms & 0x0400) ? 'S' : '-'));
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
		   (($perms & 0x0200) ? 't' : 'x' ) :
		   (($perms & 0x0200) ? 'T' : '-'));
	  return $info;
	}
}

?>