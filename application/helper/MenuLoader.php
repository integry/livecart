<?php

/**
 * Class for creating menu from backend_menu directory.
 * Also has static functions for creating js array of Tigra menu
 *
 * @package application.helper
 */
class MenuLoader {

	private	$mainMenu = array();
	private $currentTopKey;
	private $indexTopKey;
	private $reload = true;

	/**
     * Reads menu structure from files of directorie or from cache. Store if neccesary structure in cache.
	 */
	public function __construct()
	{
	  	$cache_file = ClassLoader::getRealPath("cache.configuration.backend_menu");

	  	if ($this->reload || !file_exists($cache_file))
		{
		  	MenuLoader::createFromDir($this->mainMenu, ClassLoader::getRealPath("application.configuration.backend_menu"));
//			$this->sortMenu();
			if (!is_dir(dirname($cache_file)))
			{
                mkdir(dirname($cache_file));
            }
            file_put_contents($cache_file, serialize($this->mainMenu));
		}
		else
		{
			$this->mainMenu = unserialize(file_get_contents($cache_file));
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
  	 * Returns all menu hierarchy.
  	 */
  	public function &getAllHierarchy()
  	{
	    return $this->mainMenu;
	}

	/**
     *
     */
  	public function getTopList()
  	{
		$array = array();
	    $i = 0;
	    foreach ($this->mainMenu as $menu)
	    {
		  	$array[$i]['title'] = $menu['title'];
			$array[$i]['order'] = $menu['order'];
			$array[$i]['controller'] = $menu['controller'];
			$array[$i]['action'] = $menu['action'];
			$i ++;
		}
		return $array;
	}

	/**
	 * Returns second level menu hierarchy.
	 * @param string $controller Name of controller
	 * @param string $action Name of action
	 */
	public function &getCurrentHierarchy($controller, $action)
	{
	  	if (!empty($this->currentTopKey))
	  	{
			unset($this->currentTopKey);
		}

		if (!empty($this->indexTopKey))
		{
			unset($this->indexTopKey);
		}

	  	$this->findCurrentHierarchy($controller, $action);

		if (!empty($this->currentTopKey))
		{
			return $this->mainMenu[$this->currentTopKey];
		}
		else
		{
	  		return $this->mainMenu[$this->indexTopKey];
	  	}
	}

	private function findCurrentHierarchy($controller, $action, &$menu = null, $currentTop = 0)
	{
	  	if ($menu == null)
	  	{
		    $menu = &$this->mainMenu;
		    $level = 1;
		}

	  	foreach ($menu as $key  => $child)
	  	{
	  	  	if (!empty($level))
	  	  	{
				$currentTop = $key;
			}

		    if ($child['controller'] == $controller && $child['action'] == $action)
			{
				$this->currentTopKey = $currentTop;
			}

			if ($child['controller'] == $controller && $child['action'] == 'index')
			{
				$this->indexTopKey = $currentTop;
			}

			if (!empty($child['items']) && count($child['items']) > 0)
			{
				$this->findCurrentHierarchy($controller, $action, $child['items'], $currentTop);
			}
		}
	}

  	private static function createFromDir(&$father_menu, $path)
  	{

		$iter = new DirectoryIterator($path);
	  	$files = array();
	  	foreach ($iter as $value)
	  	{
		    if (($value->isFile()) && (".xml" == strtolower(substr($value->getFileName(), -4))))
		    {
				$files[] = $value->getFileName();
			}
		}

		asort($files);

	  	foreach ($files as $file)
	  	{
			// gets simple xml stucture
			$struct = simplexml_load_file($path.'/'.$file);
			MenuLoader::createFromXML($father_menu, $struct);
			$subDir = $path.'/'.substr($file, 0, -4);
			if (file_exists($subDir))
			{
				MenuLoader::createFromDir($father_menu[count($father_menu)]['items'], $subDir);
			}
		}
	}


	private static function createFromXML(&$father_menu, $struct)
	{
	  	$i = count($father_menu) + 1;
	  	$father_menu[$i]['title'] = (string)$struct->Title;
	  	$father_menu[$i]['order'] = (string)$struct->Order;
	  	$father_menu[$i]['controller'] = (string)$struct->Controller;
	  	$father_menu[$i]['action'] = (string)$struct->Action;
	  	$father_menu[$i]['role'] = (string)$struct->Role;

	  	if (empty($father_menu[$i]['action']))
	  	{
			$father_menu[$i]['action'] = 'index';
		}

	  	if ($struct->Items)
	  	{
		  	foreach($struct->Items->Menu as $value)
		  	{
				MenuLoader::createFromXML($father_menu[$i]['items'], $value);
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