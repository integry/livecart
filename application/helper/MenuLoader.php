<?php

/**
 * Class for creating menu from backend_menu directory.
 *
 * @package application.helper
 * @author Integry Systems
 */
class MenuLoader
{
	private	$mainMenu = array();

	/**
	 * Reads menu structure from files of directorie or from cache. Store if neccesary structure in cache.
	 */
	public function __construct(LiveCart $application)
	{
		$this->application = $application;

		foreach ($this->application->getConfigContainer()->getModuleDirectories() as $dir)
		{
			$dir .= '/application/configuration/backend_menu';
			if (file_exists($dir))
			{
				$this->createFromDir($this->mainMenu, $dir);
			}
		}
	}

	/**
	 */
  	public function sortMenu()
  	{
		uasort($this->mainMenu, array($this, 'CompareOrders'));
		foreach ($this->mainMenu as $key => $value)
		{
			if (!empty($this->mainMenu[$key]['items']) && is_array($this->mainMenu[$key]['items']))
			{
				uasort($this->mainMenu[$key]['items'], array($this, 'CompareOrders'));
			}
		}
	}

	/**
	 * Returns second level menu hierarchy.
	 * @param string $controller Name of controller
	 * @param string $action Name of action
	 */
	public function &getCurrentHierarchy($controller, $action)
	{
	  	return $this->mainMenu;
	}

  	private function createFromDir(&$father_menu, $path)
  	{
		$iter = new DirectoryIterator($path);
	  	$files = array();
	  	foreach ($iter as $value)
	  	{
			if (($value->isFile()) && (".js" == strtolower(substr($value->getFileName(), -3))))
			{
				$files[] = $value->getFileName();
			}
		}

	  	foreach ($files as $file)
	  	{
			// gets simple xml stucture
			$struct = json_decode(file_get_contents($path.'/'.$file), true);
			$father_menu = array_merge_recursive($father_menu, $struct);

			$subDir = $path.'/'.substr($file, 0, -3);
			if (file_exists($subDir))
			{
				$this->createFromDir($father_menu[count($father_menu)]['items'], $subDir);
			}
		}
	}

	private function CompareOrders($a, $b)
	{
		if ($a['order'] == $b['order'])
		{
			return 0;
		}
		return ($a['order'] < $b['order']) ? -1 : 1;
	}
}

?>